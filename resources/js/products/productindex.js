
document.addEventListener('alpine:init', () => {
    Alpine.data('shop', () => ({
        viewMode: 'grid',
        showFilters: false,
        cartCount: 0,
        wishlistCount: 0,
        wishlistItems: [],
        loading: false,
        filterLoading: false,
        notifications: [],
        notificationIdCounter: 0,
        filters: {
            category: new URLSearchParams(window.location.search).get('category') || 'all',
            priceMin: (() => { const v = new URLSearchParams(window.location.search).get('price_min'); return v !== null ? Number(v) : null; })(),
            priceMax: (() => { const v = new URLSearchParams(window.location.search).get('price_max'); return v !== null ? Number(v) : null; })(),
            inStock: Boolean(new URLSearchParams(window.location.search).get('in_stock')),
            onSale: Boolean(new URLSearchParams(window.location.search).get('on_sale')),
            sort: new URLSearchParams(window.location.search).get('sort') || ''
        },

        init() {
            this.updateCartCount();
            this.updateWishlistCount();
            this.loadWishlistItems();
        },

        isInWishlist(productId) {
            return this.wishlistItems.includes(Number(productId));
        },

        async loadWishlistItems() {
            try {
                const res = await fetch('/wishlist/items', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.wishlistItems = Array.isArray(data.items) ? data.items.map(i => Number(i)) : [];
                this.wishlistCount = data.count ?? this.wishlistItems.length;
            } catch (e) { console.warn('Failed loading wishlist', e); }
        },

        async toggleWishlist(productId, productName = 'Product') {
            const id = Number(productId);
            const already = this.isInWishlist(id);
            if (!already) this.wishlistItems.push(id);
            else this.wishlistItems = this.wishlistItems.filter(x => x !== id);

            try {
                const url = already ? `/wishlist/remove/${id}` : `/wishlist/add/${id}`;
                const resp = await fetch(url, {
                    method: already ? 'DELETE' : 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const json = await resp.json();
                if (json.success) {
                    this.wishlistCount = json.wishlistCount ?? this.wishlistItems.length;
                    this.showNotification(already ? 'removed from wishlist' : 'added to wishlist', 'success', productName);
                } else {
                    if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                    else this.wishlistItems.push(id);
                    this.showNotification(json.message || 'Failed', 'error', productName);
                }
            } catch (err) {
                if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                else this.wishlistItems.push(id);
                this.showNotification('Network error', 'error', productName);
            }
        },

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            this.notifications.push({ id, message, type, productName, show: true });
            if (this.notifications.length > 5) this.removeNotification(this.notifications[0].id);
            setTimeout(() => this.removeNotification(id), 4000);
        },

        removeNotification(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx !== -1) {
                this.notifications[idx].show = false;
                setTimeout(() => { this.notifications = this.notifications.filter(n => n.id !== id); }, 500);
            }
        },

        async addToCart(productId, productName = 'Product') {
            this.loading = true;
            try {
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    this.cartCount = data.cartCount;
                    this.showNotification('added to cart', 'success', productName);
                } else {
                    this.showNotification(data.message || 'Failed', 'error', productName);
                }
            } catch (error) {
                this.showNotification('Error adding to cart', 'error', productName);
            } finally {
                this.loading = false;
            }
        },

        async updateCartCount() {
            try {
                const res = await fetch('/cart/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.cartCount = data.count;
            } catch (e) { console.error(e); }
        },

        async updateWishlistCount() {
            try {
                const res = await fetch('/wishlist/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.wishlistCount = data.count;
            } catch (e) { console.error(e); }
        },

        applyFilters() {
            if (this.filterLoading) return;
            this.filterLoading = true;
            const params = new URLSearchParams();
            if (this.filters.category && this.filters.category !== 'all') params.set('category', this.filters.category);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            if (this.filters.priceMin !== null) params.set('price_min', this.filters.priceMin);
            if (this.filters.priceMax !== null) params.set('price_max', this.filters.priceMax);
            if (this.filters.inStock) params.set('in_stock', '1');
            if (this.filters.onSale) params.set('on_sale', '1');
            window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        },

        clearFilters() {
            this.filters = { category: 'all', priceMin: null, priceMax: null, inStock: false, onSale: false, sort: '' };
            this.applyFilters();
        }
    }));
});
function updateGlobalCount(type, n = 1) {
    const apply = () => {
        try {
            if (window.Alpine && Alpine.store && Alpine.store('global')) {
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
        notifications: [],
        notificationIdCounter: 0,

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
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.showFilters) {
                    this.showFilters = false;
                }
            });
        },

        isInWishlist(productId) {
            const id = Number(productId);
            return this.wishlistItems.some(item => Number(item) === id);
        },

        async loadWishlistItems() {
            try {
                const res = await fetch('/wishlist/items', { 
                    credentials: 'same-origin', 
                    headers: { 'Accept': 'application/json' } 
                });
                const data = await res.json();
                this.wishlistItems = Array.isArray(data.items) 
                    ? data.items.map(i => Number(i)) 
                    : [];
                this.wishlistCount = data.count ?? this.wishlistItems.length;
            } catch (e) {
                console.warn('Failed loading wishlist items', e);
            }
        },

        async toggleWishlist(productId, productName = 'Product') {
            const id = Number(productId);
            const already = this.isInWishlist(id);

            if (!already) {
                this.wishlistItems.push(id);
                updateGlobalCount('wishlist', 1);
            } else {
                this.wishlistItems = this.wishlistItems.filter(x => Number(x) !== id);
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
                    if (!already) this.wishlistItems = this.wishlistItems.filter(x => Number(x) !== id);
                    else this.wishlistItems.push(id);
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
                if (!already) this.wishlistItems = this.wishlistItems.filter(x => Number(x) !== id);
                else this.wishlistItems.push(id);
                this.showNotification('Network error', 'error', productName);
                console.error(err);
            }
        },

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            const notification = { id, message, type, productName, show: true };
            this.notifications.push(notification);

            if (this.notifications.length > 5) {
                this.removeNotification(this.notifications[0].id);
            }

            setTimeout(() => this.removeNotification(id), 4000);
        },

        removeNotification(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index !== -1) {
                this.notifications[index].show = false;
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
                    this.showNotification(data.message || 'Failed to add to cart', 'error', productName);
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
            this.filters.priceMin = (pMin === '' || pMin === null) ? null : Number(pMin);
            this.filters.priceMax = (pMax === '' || pMax === null) ? null : Number(pMax);

            if (this.filters.priceMin !== null && this.filters.priceMax !== null) {
                if (this.filters.priceMin > this.filters.priceMax) {
                    [this.filters.priceMin, this.filters.priceMax] = [this.filters.priceMax, this.filters.priceMin];
                }
            }
        },

        applyFilters() {
            if (this.filterLoading) return;
            this.filterLoading = true;

            this.normalizeFilters();
            const params = new URLSearchParams();

            if (this.filters.category && this.filters.category !== 'all') params.set('category', this.filters.category);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            if (this.filters.priceMin !== null) params.set('price_min', this.filters.priceMin);
            if (this.filters.priceMax !== null) params.set('price_max', this.filters.priceMax);
            if (this.filters.inStock) params.set('in_stock', '1');
            if (this.filters.onSale) params.set('on_sale', '1');

            window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        },

        clearFilters() {
            this.filters = { category: 'all', priceMin: null, priceMax: null, inStock: false, onSale: false, sort: '' };
            this.applyFilters();
        }
    };
}

// Expose globally
window.shop = shopFactory;

// Register with Alpine
document.addEventListener('alpine:init', () => {
    Alpine.data('shop', shopFactory);
});

console.log('Products JS loaded, shop registered');