/**
 * Helper: get translation from window object
 */
function t(key, fallback = '') {
    return window.wishlistTranslations?.[key] || fallback;
}

/**
 * Helper: update global Alpine store counts safely
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
 * wishlistPage factory
 */
function wishlistPageFactory() {
    return {
        loading: false,
        notifications: [],
        notificationIdCounter: 0,

        init() {
            console.log('Wishlist page initialized');
        },

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            const notification = {
                id,
                message,
                type,
                productName,
                show: true,
                hiding: false
            };

            this.notifications.push(notification);

            // Limit to 4 visible notifications
            if (this.notifications.length > 4) {
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
            if (index !== -1 && !this.notifications[index].hiding) {
                // Mark as hiding to trigger exit animation
                this.notifications[index].hiding = true;
                this.notifications[index].show = false;
                
                // Remove from array after animation completes
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 400);
            }
        },

        async removeFromWishlist(productId, productName = 'Product') {
            try {
                const response = await fetch(`/wishlist/remove/${productId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showNotification(t('removed', 'Removed from wishlist'), 'info', productName);
                    
                    // Плавно обновляем счётчик в navbar
                    if (typeof window.updateWishlistCount === 'function') {
                        window.updateWishlistCount(data.count);
                    } else if (typeof window.decrementWishlistCount === 'function') {
                        window.decrementWishlistCount();
                    }
                    
                    // Находим карточку товара и удаляем её с анимацией ВЛЕВО
                    const card = document.querySelector(`[data-product-id="${productId}"]`);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(-100px)';
                        card.style.transition = 'all 0.3s ease';
                        
                        setTimeout(() => {
                            card.remove();
                            
                            // Проверяем остались ли товары
                            const remaining = document.querySelectorAll('[data-product-id]').length;
                            if (remaining === 0) {
                                // Показываем empty state без перезагрузки
                                const container = document.querySelector('.wishlist-grid');
                                if (container) {
                                    const emptyTitle = t('empty_title', 'Your wishlist is empty');
                                    const emptyText = t('empty_text', 'Start adding products you love!');
                                    const browseBtn = t('browse_products', 'Browse Products');
                                    container.innerHTML = `
                                        <div class="col-span-full text-center py-16">
                                            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <h3 class="text-xl font-medium text-gray-500 mb-2">${emptyTitle}</h3>
                                            <p class="text-gray-400 mb-6">${emptyText}</p>
                                            <a href="/products" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                                                ${browseBtn}
                                            </a>
                                        </div>
                                    `;
                                }
                            }
                        }, 300);
                    }
                } else {
                    this.showNotification(data.message || t('failed_remove', 'Failed to remove'), 'error', productName);
                }
            } catch (error) {
                console.error('Remove error:', error);
                this.showNotification(t('network_error', 'Network error'), 'error', productName);
            }
        },

        async addToCart(productId, productName = 'Product', variantLabel = '', variantId = null) {
            if (this.loading) return;
            
            this.loading = true;

            try {
                const payload = { quantity: 1 };
                if (variantId) payload.variant_id = variantId;

                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (data.success) {
                    const what = variantLabel ? ` (${variantLabel})` : '';
                    this.showNotification((t('added_to_cart', 'Added to cart') + what), 'success', productName + what);
                    
                    // Плавно обновляем счётчик корзины в navbar
                    if (typeof window.updateCartCount === 'function' && data.cartCount !== undefined) {
                        window.updateCartCount(data.cartCount);
                    }
                } else {
                    this.showNotification(data.message || t('failed_add', 'Failed to add to cart'), 'error', productName);
                }
            } catch (error) {
                console.error('Add to cart error:', error);
                this.showNotification(t('error_adding_cart', 'Error adding to cart'), 'error', productName);
            } finally {
                this.loading = false;
            }
        }
    };
}

/**
 * Register with Alpine
 */
function registerWishlistComponents() {
    if (!window.Alpine) return;

    Alpine.data('wishlistPage', wishlistPageFactory);

    try {
        document.querySelectorAll('[x-data="wishlistPage()"]').forEach(el => {
            if (typeof Alpine.initTree === 'function') Alpine.initTree(el);
        });
    } catch (e) {
        console.warn('Alpine initTree failed', e);
    }
}

if (window.Alpine) {
    registerWishlistComponents();
} else {
    document.addEventListener('alpine:init', registerWishlistComponents);
}

window.wishlistPage = wishlistPageFactory;

console.log('Wishlist JS loaded');