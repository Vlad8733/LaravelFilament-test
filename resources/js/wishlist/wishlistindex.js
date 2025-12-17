// Factory that returns component state & methods (keeps parity with previous code)
function wishlistFactory() {
    return {
        loading: false,
        notification: { show: false, message: '', type: 'success' },

        showNotification(message, type = 'success') {
            this.notification.message = message;
            this.notification.type = type;
            this.notification.show = true;
            setTimeout(() => this.notification.show = false, 3000);

            // also update DOM fallback toast
            showWishlistNotification(message, type);
        },

        async removeFromWishlist(productId) {
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
                    this.showNotification(data.message || 'Removed from wishlist', 'success');
                    // remove card from DOM if present
                    const btn = document.querySelector('[data-wishlist-remove="' + productId + '"]');
                    if (btn) {
                        const card = btn.closest('.wishlist-card');
                        if (card) card.remove();
                    }
                    try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('wishlist', -1); } catch(e){}
                } else {
                    this.showNotification(data.message || 'Failed to remove from wishlist', 'error');
                }
            } catch (err) {
                console.error('removeFromWishlist error', err);
                this.showNotification('Network error', 'error');
            } finally {
                this.loading = false;
            }
        },

        async addToCart(productId) {
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
                    this.showNotification(data.message || 'Product added to cart!', 'success');
                    try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', 1); } catch(e){}
                } else {
                    this.showNotification(data.message || 'Failed to add to cart', 'error');
                }
            } catch (err) {
                console.error('addToCart error', err);
                this.showNotification('Network error', 'error');
            } finally {
                this.loading = false;
            }
        }
    };
}

// expose global factory (so x-data="wishlistPage()" works even if Alpine not ready)
window.wishlistPage = function() {
    return wishlistFactory();
};

// Register with Alpine when it initializes (ensures Alpine recognizes wishlistPage)
document.addEventListener('alpine:init', () => {
    try {
        Alpine.data('wishlistPage', () => wishlistFactory());
    } catch (e) {
        // ignore if Alpine not present
        console.warn('Alpine not present at alpine:init registration', e);
    }
});

/* Helper that shows a DOM toast fallback (kept separate so global fallback functions can reuse) */
function showWishlistNotification(message, type = 'success') {
    try {
        // Try to sync with Alpine component if present
        const root = document.querySelector('[x-data="wishlistPage()"]');
        if (root && root.__x && root.__x.$data && root.__x.$data.notification) {
            root.__x.$data.notification.message = message;
            root.__x.$data.notification.type = type;
            root.__x.$data.notification.show = true;
            setTimeout(() => { root.__x.$data.notification.show = false; }, 3000);
            return;
        }
    } catch (err) {
        // ignore
    }

    // DOM fallback: find .toast inside wishlist page
    const toast = document.querySelector('.wishlist-page .toast');
    if (!toast) {
        if (type === 'error') alert(message); else console.info(message);
        return;
    }
    toast.classList.remove('bg-green-500', 'bg-red-500');
    toast.classList.add(type === 'success' ? 'bg-green-500' : 'bg-red-500');
    const span = toast.querySelector('span');
    if (span) span.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

// --- Global fallback functions (for buttons that call global functions or when Alpine not initialized) ---
window.removeFromWishlist = window.removeFromWishlist || async function(productId) {
    // reuse the same logic as in component but via fetch + notification helper
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
            showWishlistNotification(data.message || 'Removed from wishlist', 'success');
            const btn = document.querySelector('[data-wishlist-remove="' + productId + '"]');
            if (btn) {
                const card = btn.closest('.wishlist-card');
                if (card) card.remove();
            }
            try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('wishlist', -1); } catch(e){}
            return true;
        } else {
            showWishlistNotification(data.message || 'Failed to remove from wishlist', 'error');
            return false;
        }
    } catch (err) {
        console.error('removeFromWishlist fallback error', err);
        showWishlistNotification('Network error', 'error');
        return false;
    }
};

window.addToCart = window.addToCart || async function(productId) {
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
            showWishlistNotification(data.message || 'Product added to cart!', 'success');
            try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', 1); } catch(e){}
            return true;
        } else {
            showWishlistNotification(data.message || 'Failed to add to cart', 'error');
            return false;
        }
    } catch (err) {
        console.error('addToCart fallback error', err);
        showWishlistNotification('Network error', 'error');
        return false;
    }
};