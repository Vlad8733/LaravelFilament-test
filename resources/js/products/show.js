window.productPage = function() {
    return {
        selectedImage: 0,
        quantity: 1,
        maxQuantity: 1,
        productId: null,
        selectedVariantId: null,
        loading: false,

        init(maxQty = 1, productId = null) {
            this.maxQuantity = Number(maxQty) || 1;
            this.productId = productId;
            // FORCE default quantity = 1 and sync with DOM / Alpine
            this.quantity = (this.quantity && Number(this.quantity) > 0) ? Number(this.quantity) : 1;
            const q = document.getElementById('product-quantity');
            if (q) {
                q.value = String(this.quantity);
                q.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        onVariantChange(e) {
            // called when variant select changes
            const sel = e && e.target ? e.target : document.getElementById('product-variant');
            if (!sel) return;
            const val = sel.value || null;
            this.selectedVariantId = val || null;

            const priceEl = document.getElementById('product-price');
            const oldEl = document.getElementById('product-old-price');
            const discEl = document.getElementById('product-discount');

            if (!val) {
                // reset to product-level price/stock (use data on add button)
                const addBtn = document.querySelector('[data-add-to-cart]');
                const max = addBtn ? Number(addBtn.getAttribute('data-max') || Infinity) : Infinity;
                this.maxQuantity = isFinite(max) ? max : this.maxQuantity;
                // price fallback: if oldEl has class hidden, use its text otherwise keep priceEl as-is
                if (priceEl && priceEl.dataset && priceEl.dataset.basePrice) {
                    priceEl.textContent = `$${Number(priceEl.dataset.basePrice).toFixed(2)}`;
                }
                if (oldEl) oldEl.classList.add('hidden');
                if (discEl) discEl.classList.add('hidden');
                return;
            }

            const option = sel.querySelector(`option[value="${val}"]`);
            if (!option) return;
            const vPrice = option.getAttribute('data-price');
            const vSale = option.getAttribute('data-sale');
            const vStock = option.getAttribute('data-stock');

            // update displayed price
            if (priceEl) {
                const display = (vSale && vSale !== '0') ? Number(vSale) : Number(vPrice);
                priceEl.textContent = `$${Number(display).toFixed(2)}`;
            }

            // show old price if sale exists and differs
            if (vSale && Number(vSale) > 0 && oldEl) {
                oldEl.textContent = `$${Number(vPrice).toFixed(2)}`;
                oldEl.classList.remove('hidden');
                if (discEl) {
                    const perc = Math.round((1 - (Number(vSale) / Number(vPrice || vSale))) * 100);
                    const offText = (discEl.dataset && discEl.dataset.offText) ? discEl.dataset.offText : 'off';
                    discEl.textContent = `${perc}% ${offText}`;
                    discEl.classList.remove('hidden');
                }
            } else if (oldEl) {
                oldEl.classList.add('hidden');
                if (discEl) discEl.classList.add('hidden');
            }

            // update max quantity
            this.maxQuantity = (vStock !== null && vStock !== undefined && vStock !== '') ? Number(vStock) : this.maxQuantity;
            // ensure quantity does not exceed new max
            if (Number(this.quantity) > Number(this.maxQuantity)) this.quantity = this.maxQuantity;
            const qEl = document.getElementById('product-quantity');
            if (qEl) {
                qEl.setAttribute('max', String(this.maxQuantity));
                qEl.value = String(this.quantity);
                qEl.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        get canAddToCart() {
            return this.maxQuantity > 0 && this.quantity >= 1 && this.quantity <= this.maxQuantity;
        },

        async addToCart() {
            // prevent adding if requested quantity exceeds stock
            if (this.maxQuantity !== undefined && Number(this.quantity) > Number(this.maxQuantity)) {
                const req = Number(this.quantity);
                const avail = Number(this.maxQuantity);
                window.showToast(`Requested quantity (${req}) not available. Only ${avail} in stock.`, 'error');
                return;
            }

            if (!this.canAddToCart) return;
            this.loading = true;
            try {
                const response = await fetch(`/cart/add/${this.productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: this.quantity, variant_id: this.selectedVariantId })
                });

                const data = await response.json();

                if (data.success) {
                    window.showToast('Product added to cart successfully!', 'success');
                    try { if (Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', 1); } catch(e){}
                } else {
                    window.showToast(data.message || 'Error adding product to cart', 'error');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                window.showToast('Error adding product to cart', 'error');
            } finally {
                this.loading = false;
            }
        }
    };
};

// global toast helper
window.showToast = function(message, type = 'success') {
    const containerId = 'toast-container';
    let container = document.getElementById(containerId);
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-green-500' : 'bg-red-500';

    toast.innerHTML = `
        <div class="${bgClass} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full">
            ${message}
        </div>
    `;

    container.appendChild(toast);

    // animate in
    setTimeout(() => {
        if (toast.firstElementChild) toast.firstElementChild.classList.remove('translate-x-full');
    }, 10);

    // remove after 3s
    setTimeout(() => {
        if (toast.firstElementChild) toast.firstElementChild.classList.add('translate-x-full');
        setTimeout(() => {
            if (container.contains(toast)) container.removeChild(toast);
        }, 300);
    }, 3000);
};

// Fallback DOM handlers so UI works even if Alpine isn't ready
document.addEventListener('DOMContentLoaded', () => {
    // ensure initial quantity visible and set to 1 if missing/invalid
    let qtyInput = document.getElementById('product-quantity');
    if (qtyInput) {
        const current = Number(qtyInput.value);
        if (!current || current < 1) {
            qtyInput.value = '1';
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    // Thumbnail clicks -> update main image
    document.querySelectorAll('.thumb-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const src = btn.getAttribute('data-thumb-src');
            if (!src) return;
            const main = document.getElementById('main-product-image');
            if (main) main.src = src;
        });
    });

    // Quantity buttons fallback
    document.querySelectorAll('[data-qty-action]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!qtyInput) qtyInput = document.getElementById('product-quantity');
            if (!qtyInput) return;
            const action = btn.getAttribute('data-qty-action');
            let val = Number(qtyInput.value) || 1;
            if (action === 'decrement') val = Math.max(1, val - 1);
            if (action === 'increment') {
                const max = Number(qtyInput.getAttribute('max')) || Infinity;
                val = Math.min(max, val + 1);
            }
            qtyInput.value = val;
            // dispatch input so Alpine (x-model) picks up the change if present
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    // Fallback Add to Cart (works without Alpine)
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            // if Alpine handled it already, let it run (avoid double submit)
            // but still provide fallback for non-Alpine case
            const productId = btn.getAttribute('data-product-id');
            const qtyEl = document.getElementById('product-quantity');
            const qty = Number(qtyEl ? qtyEl.value : 1) || 1;
            const maxAttr = btn.getAttribute('data-max');
            const maxAvail = (maxAttr !== null) ? Number(maxAttr) : Infinity;

            // check stock before sending
            if (Number(qty) > Number(maxAvail)) {
                window.showToast(`Requested quantity (${qty}) not available. Only ${maxAvail} in stock.`, 'error');
                return;
            }

            // simple UI feedback
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.textContent = 'Adding...';

            try {
                // include variant_id if present in select
                const variantSelect = document.getElementById('product-variant');
                const variantId = variantSelect ? variantSelect.value || null : null;

                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: qty, variant_id: variantId })
                });
                const data = await response.json();

                if (data && data.success) {
                    window.showToast(data.message || 'Added to cart', 'success');
                    // update global Alpine store if present
                    try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', 1); } catch(e){}
                } else {
                    window.showToast((data && data.message) || 'Failed to add to cart', 'error');
                }
            } catch (err) {
                console.error('Fallback addToCart error', err);
                window.showToast('Network error', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
});