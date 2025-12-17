/**
 * Factory for cart page data — возвращаем объект данных/методов
 */
function cartFactory() {
    return {
        couponCode: '',

        async updateQuantity(productId, quantity) {
            if (quantity < 1) return this.removeItem(productId);
            try {
                const response = await fetch(`/cart/update/${productId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ quantity })
                });
                if (response.ok) location.reload();
            } catch (error) {
                console.error('Error updating quantity:', error);
            }
        },

        async removeItem(productId) {
            try {
                const response = await fetch(`/cart/remove/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (response.ok) location.reload();
            } catch (error) {
                console.error('Error removing item:', error);
            }
        },

        async applyCoupon() {
            if (!this.couponCode || !this.couponCode.trim()) return;
            try {
                const response = await fetch('/cart/coupon/apply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ code: this.couponCode })
                });
                if (response.ok) location.reload();
                else {
                    const data = await response.json();
                    alert(data.message || 'Invalid coupon code');
                }
            } catch (error) {
                console.error('Error applying coupon:', error);
            }
        },

        async removeCoupon() {
            try {
                const response = await fetch('/cart/coupon/remove', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                if (response.ok) location.reload();
            } catch (error) {
                console.error('Error removing coupon:', error);
            }
        }
    };
}

// helper to register and init existing nodes if Alpine already ran
function registerCartComponent() {
    if (!window.Alpine) return;
    Alpine.data('cartPage', cartFactory);

    // if Alpine already initialized the DOM earlier, initialize only the cart nodes now
    try {
        document.querySelectorAll('[x-data="cartPage()"]').forEach(el => {
            // Alpine.initTree exists in Alpine v3 — initialize subtree
            if (typeof Alpine.initTree === 'function') {
                Alpine.initTree(el);
            }
        });
    } catch (e) {
        // silently ignore if API differs
        console.warn('Alpine initTree failed', e);
    }
}

// register at the right time
if (window.Alpine) {
    registerCartComponent();
} else {
    document.addEventListener('alpine:init', registerCartComponent);
}

// also expose factory as global function so x-data="cartPage()" works regardless of timing
window.cartPage = cartFactory;