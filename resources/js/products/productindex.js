/**
 * Helper: update global Alpine store counts safely (immediate or deferred)
 */
function updateGlobalCount(type, n = 1) {
    const apply = () => {
        try {
            if (Alpine && Alpine.store && Alpine.store('global')) {
                Alpine.store('global').increment(type, n);
            }
        } catch (e) {
            // silent
        }
    };

    if (window.Alpine && Alpine.store && Alpine.store('global')) {
        apply();
    } else {
        document.addEventListener('alpine:init', () => {
            apply();
        }, { once: true });
    }
}

/**
 * shop factory (used as x-data="shop()")
 */
function shopFactory() {
    return {
        viewMode: 'grid',
        showFilters: false,
        cartCount: 0,
        wishlistCount: 0,
        wishlistItems: [],
        loading: false,
        filterLoading: false,
        notifications: [], // Changed from single notification to array
        notificationIdCounter: 0,

        // filters: priceMin/priceMax are nullable -> absence = no limit
        filters: {
            category: new URLSearchParams(window.location.search).get('category') || 'all',
            priceMin: (() => {
                const v = new URLSearchParams(window.location.search).get('price_min');
                return v !== null ? Number(v) : null;
            })(),
            priceMax: (() => {
                const v = new URLSearchParams(window.location.search).get('price_max');
                return v !== null ? Number(v) : null;
            })(),
            inStock: Boolean(new URLSearchParams(window.location.search).get('in_stock')),
            onSale: Boolean(new URLSearchParams(window.location.search).get('on_sale')),
            sort: new URLSearchParams(window.location.search).get('sort') || ''
        },

        init() {
            this.updateCartCount();
            this.updateWishlistCount();
            this.loadWishlistItems();
            // close filters on ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.showFilters) {
                    this.showFilters = false;
                }
            });
        },

        isInWishlist(productId) {
            return this.wishlistItems.indexOf(productId) !== -1;
        },

        async loadWishlistItems() {
            try {
                const res = await fetch('/wishlist/items', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.wishlistItems = Array.isArray(data.items) ? data.items.map(i => Number(i)) : [];
                this.wishlistCount = data.count ?? this.wishlistItems.length;
            } catch (e) {
                console.warn('Failed loading wishlist items', e);
            }
        },

        async toggleWishlist(productId, productName = 'Product') {
            const id = Number(productId);
            const already = this.isInWishlist(id);

            // optimistic UI
            if (!already) {
                this.wishlistItems.push(id);
                updateGlobalCount && updateGlobalCount('wishlist', 1);
            } else {
                this.wishlistItems = this.wishlistItems.filter(x => x !== id);
            }

            try {
                const url = already ? `/wishlist/remove/${id}` : `/wishlist/add/${id}`;
                const method = already ? 'DELETE' : 'POST';

                const resp = await fetch(url, {
                    method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const json = await resp.json();

                if (!json.success) {
                    // revert on failure
                    if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                    else this.wishlistItems.push(id);
                    if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('global')) {
                        Alpine.store('global').wishlistCount = Math.max(0, Alpine.store('global').wishlistCount - (already ? 0 : 1));
                    }
                    this.showNotification(json.message || 'Wishlist update failed', 'error', productName);
                } else {
                    this.wishlistCount = json.wishlistCount ?? this.wishlistItems.length;
                    if (!already) {
                        this.showNotification('was added to wishlist', 'success', productName);
                    } else {
                        this.showNotification('was removed from wishlist', 'info', productName);
                    }
                }
            } catch (err) {
                // revert on error
                if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                else this.wishlistItems.push(id);
                if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('global')) {
                    Alpine.store('global').wishlistCount = Math.max(0, Alpine.store('global').wishlistCount - (already ? 0 : 1));
                }
                this.showNotification('Network error', 'error', productName);
                console.error(err);
            }
        },

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            const notification = {
                id,
                message,
                type,
                productName,
                show: true
            };

            // Add new notification to the END of array (it will appear at bottom)
            this.notifications.push(notification);

            // Limit to 5 notifications max - remove oldest (first in array)
            if (this.notifications.length > 5) {
                const oldestId = this.notifications[0].id;
                this.removeNotification(oldestId);
            }

            // Auto-remove after 4 seconds
            setTimeout(() => {
                this.removeNotification(id);
            }, 4000);
        },

        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index !== -1) {
                this.notifications[index].show = false;
                // Remove from array after animation completes (increased to 500ms to match new animation)
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 500);
            }
        },

        async addToCart(productId, productName = 'Product') {
            this.loading = true;
            try {
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    this.cartCount = data.cartCount;
                    updateGlobalCount('cart', 1);
                    this.showNotification('was added to cart', 'success', productName);
                } else {
                    this.showNotification(data.message, 'error', productName);
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.showNotification('Error adding to cart', 'error', productName);
            } finally {
                this.loading = false;
            }
        },

        async updateCartCount() {
            try {
                const response = await fetch('/cart/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                this.cartCount = data.count;
            } catch (error) {
                console.error('Error fetching cart count:', error);
            }
        },

        async updateWishlistCount() {
            try {
                const response = await fetch('/wishlist/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                this.wishlistCount = data.count;
            } catch (error) {
                console.error('Error fetching wishlist count:', error);
            }
        },

        normalizeFilters() {
            const pMin = this.filters.priceMin;
            const pMax = this.filters.priceMax;

            this.filters.priceMin = (pMin === '' || pMin === null || typeof pMin === 'undefined') ? null : Number(pMin);
            this.filters.priceMax = (pMax === '' || pMax === null || typeof pMax === 'undefined') ? null : Number(pMax);

            if (this.filters.priceMin !== null && this.filters.priceMax !== null) {
                if (Number(this.filters.priceMin) > Number(this.filters.priceMax)) {
                    const tmp = this.filters.priceMin;
                    this.filters.priceMin = this.filters.priceMax;
                    this.filters.priceMax = tmp;
                }
            }
        },

        applyFilters() {
            if (this.filterLoading) return;
            this.filterLoading = true;

            try {
                this.normalizeFilters();
                const params = new URLSearchParams();

                if (this.filters.category && this.filters.category !== 'all') params.set('category', this.filters.category);
                if (this.filters.sort) params.set('sort', this.filters.sort);

                if (this.filters.priceMin !== null && !Number.isNaN(Number(this.filters.priceMin))) {
                    params.set('price_min', Number(this.filters.priceMin));
                }
                if (this.filters.priceMax !== null && !Number.isNaN(Number(this.filters.priceMax))) {
                    params.set('price_max', Number(this.filters.priceMax));
                }

                if (this.filters.inStock) params.set('in_stock', '1');
                if (this.filters.onSale) params.set('on_sale', '1');

                const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.location.href = url;
                try { this.showFilters = false; } catch(e){}
            } catch (err) {
                console.error('applyFilters error', err);
                this.showNotification('Failed to apply filters', 'error');
                this.filterLoading = false;
            }
        },

        clearFilters() {
            this.filters = {
                category: 'all',
                priceMin: null,
                priceMax: null,
                inStock: false,
                onSale: false,
                sort: ''
            };
            this.applyFilters();
        }
    };
}

/**
 * searchBox factory (used as x-data="searchBox()")
 */
function searchBoxFactory() {
    return {
        query: '',
        results: [],
        showResults: false,
        searchTimeout: null,

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => this.performSearch(), 300);
        },

        async performSearch() {
            if (!this.query || this.query.length < 2) {
                this.results = [];
                return;
            }
            try {
                const response = await fetch(`/products/search?q=${encodeURIComponent(this.query)}`);
                this.results = await response.json();
            } catch (error) {
                console.error('Search error:', error);
                this.results = [];
            }
        }
    };
}

/**
 * Register with Alpine and expose globals
 */
function registerProductComponents() {
    if (!window.Alpine) return;

    Alpine.data('shop', shopFactory);
    Alpine.data('searchBox', searchBoxFactory);

    try {
        document.querySelectorAll('[x-data="shop()"]').forEach(el => {
            if (typeof Alpine.initTree === 'function') Alpine.initTree(el);
        });
        document.querySelectorAll('[x-data="searchBox()"]').forEach(el => {
            if (typeof Alpine.initTree === 'function') Alpine.initTree(el);
        });
    } catch (e) {
        console.warn('Alpine initTree failed', e);
    }
}

if (window.Alpine) {
    registerProductComponents();
} else {
    document.addEventListener('alpine:init', registerProductComponents);
}

window.shop = shopFactory;
window.searchBox = searchBoxFactory;