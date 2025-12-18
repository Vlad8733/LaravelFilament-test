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
            console.warn('Could not update global count', e);
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

// Factory that returns component state & methods (with notifications array)
function wishlistFactory() {
    return {
        loading: false,
        notifications: [],
        notificationIdCounter: 0,

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            const notification = {
                id,
                message,
                type,
                productName,
                show: true
            };

            // Add new notification to the END of array
            this.notifications.push(notification);

            // Limit to 5 notifications max - remove oldest
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
                // Remove from array after animation completes
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 500);
            }
        },

        async removeFromWishlist(productId, productName = 'Product') {
            this.loading = true;
            try {
                const resp = await fetch(`/wishlist/remove/${productId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                const data = await resp.json();
                
                if (data && data.success) {
                    this.showNotification('was removed from wishlist', 'info', productName);
                    
                    // Update global count
                    updateGlobalCount('wishlist', -1);
                    
                    // remove card from DOM if present
                    const btn = document.querySelector('[data-wishlist-remove="' + productId + '"]');
                    if (btn) {
                        const card = btn.closest('.wishlist-card');
                        if (card) {
                            // Fade out before removing
                            card.style.transition = 'opacity 0.3s ease';
                            card.style.opacity = '0';
                            setTimeout(() => card.remove(), 300);
                        }
                    }
                } else {
                    this.showNotification(data.message || 'Failed to remove from wishlist', 'error', productName);
                }
            } catch (err) {
                console.error('removeFromWishlist error', err);
                this.showNotification('Network error', 'error', productName);
            } finally {
                this.loading = false;
            }
        },

        async addToCart(productId, productName = 'Product') {
            this.loading = true;
            try {
                const resp = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: 1 })
                });
                const data = await resp.json();
                
                if (data && data.success) {
                    this.showNotification('was added to cart', 'success', productName);
                    
                    // Update global count
                    updateGlobalCount('cart', 1);
                } else {
                    this.showNotification(data.message || 'Failed to add to cart', 'error', productName);
                }
            } catch (err) {
                console.error('addToCart error', err);
                this.showNotification('Network error', 'error', productName);
            } finally {
                this.loading = false;
            }
        }
    };
}

// expose global factory
window.wishlistPage = function() {
    return wishlistFactory();
};

// Register with Alpine when it initializes
if (window.Alpine) {
    Alpine.data('wishlistPage', wishlistFactory);
} else {
    document.addEventListener('alpine:init', () => {
        Alpine.data('wishlistPage', wishlistFactory);
    });
}

// --- Global fallback functions (for non-Alpine contexts) ---
window.removeFromWishlist = window.removeFromWishlist || async function(productId, productName = 'Product') {
    try {
        const resp = await fetch(`/wishlist/remove/${productId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        const data = await resp.json();
        if (data && data.success) {
            const btn = document.querySelector('[data-wishlist-remove="' + productId + '"]');
            if (btn) {
                const card = btn.closest('.wishlist-card');
                if (card) {
                    card.style.transition = 'opacity 0.3s ease';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 300);
                }
            }
            updateGlobalCount('wishlist', -1);
            return true;
        }
        return false;
    } catch (err) {
        console.error('removeFromWishlist fallback error', err);
        return false;
    }
};

window.addToCart = window.addToCart || async function(productId, productName = 'Product') {
    try {
        const resp = await fetch(`/cart/add/${productId}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: 1 })
        });
        const data = await resp.json();
        if (data && data.success) {
            updateGlobalCount('cart', 1);
            return true;
        }
        return false;
    } catch (err) {
        console.error('addToCart fallback error', err);
        return false;
    }
};

// Debug information
console.log('Wishlist JS loaded');
document.addEventListener('alpine:init', () => {
    console.log('Alpine initialized for wishlist');
});