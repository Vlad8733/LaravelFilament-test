// Product page Alpine component
let productPageRegistered = false;

function registerProductPageComponent() {
    if (productPageRegistered) return true;
    if (typeof Alpine === 'undefined') return false;
    
    Alpine.data('productPage', () => ({
        selectedImage: 0,
        quantity: 1,
        maxQuantity: 1,
        productId: null,
        selectedVariantId: null,
        loading: false,
        // Toast notifications (Alpine-driven)
        notifications: [],
        notificationIdCounter: 0,

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
            // attach variant button handlers if present
            this.attachVariantButtons();
        },

        attachVariantButtons() {
            const container = document.getElementById('product-variant-buttons');
            if (!container) return;
            container.querySelectorAll('.variant-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const id = btn.getAttribute('data-variant-id');
                    this.selectVariant(id, btn);
                });
            });
        },

        selectVariant(variantId, btnEl = null) {
            // find button element if not provided
            let btn = btnEl;
            if (!btn) btn = document.querySelector(`.variant-btn[data-variant-id="${variantId}"]`);
            if (!btn) return;
            // mark active (visual)
            document.querySelectorAll('.variant-btn').forEach(b => {
                b.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400');
                b.classList.add('bg-white');
            });
            btn.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50', 'border-blue-400');

            // set selected variant id
            this.selectedVariantId = String(variantId || null);

            // update UI from button data
            this.updateVariantUIFromElement(btn);
        },

        updateVariantUIFromElement(el) {
            const priceEl = document.getElementById('product-price');
            const oldEl = document.getElementById('product-old-price');
            const discEl = document.getElementById('product-discount');
            const infoEl = document.getElementById('product-variant-info');

            const vPrice = el.getAttribute('data-price');
            const vSale = el.getAttribute('data-sale');
            const vStock = el.getAttribute('data-stock');
            const vSku = el.getAttribute('data-sku');
            const vAttrs = el.getAttribute('data-attrs');

            if (priceEl) {
                const display = (vSale && vSale !== '0') ? Number(vSale) : Number(vPrice);
                priceEl.textContent = `$${Number(display).toFixed(2)}`;
            }

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

            // update max quantity and quantity field
            this.maxQuantity = (vStock !== null && vStock !== undefined && vStock !== '') ? Number(vStock) : this.maxQuantity;
            if (Number(this.quantity) > Number(this.maxQuantity)) this.quantity = this.maxQuantity;
            const qEl = document.getElementById('product-quantity');
            if (qEl) {
                qEl.setAttribute('max', String(this.maxQuantity));
                qEl.value = String(this.quantity);
                qEl.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // update variant info area (nice badges)
            if (infoEl) {
                const badges = [];
                const t = window.productTranslations || {};
                if (vSku) badges.push(`<span class="inline-block px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 rounded">SKU: ${vSku}</span>`);
                if (vAttrs) {
                    // split attributes by comma and render chips
                    const parts = vAttrs.split(',').map(p => p.trim()).filter(Boolean);
                    parts.forEach(p => badges.push(`<span class="inline-block px-2 py-0.5 text-xs text-gray-600 bg-white border rounded">${p}</span>`));
                }
                if (vStock !== null && vStock !== undefined) {
                    const inStockText = (t.in_stock_count || ':count in stock').replace(':count', vStock);
                    const outOfStockText = t.out_of_stock || 'Out of stock';
                    const stockBadge = Number(vStock) > 0 ? `<span class="inline-block px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-800 rounded">${inStockText}</span>` : `<span class="inline-block px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-800 rounded">${outOfStockText}</span>`;
                    badges.push(stockBadge);
                }
                infoEl.innerHTML = badges.join(' ');
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

        async addToCart(productId = null) {
            if (!productId) productId = this.productId;
            const t = window.productTranslations || {};
            // prevent adding if requested quantity exceeds stock
            if (this.maxQuantity !== undefined && Number(this.quantity) > Number(this.maxQuantity)) {
                const req = Number(this.quantity);
                const avail = Number(this.maxQuantity);
                const msg = (t.requested_qty_not_available || 'Requested quantity (:requested) not available. Only :available in stock.')
                    .replace(':requested', req).replace(':available', avail);
                this.showNotification(msg, 'error');
                return;
            }

            if (!this.canAddToCart) return;
            this.loading = true;
            try {
                const payload = { quantity: this.quantity, variant_id: this.selectedVariantId };
                console.debug('Adding to cart', { productId, payload });
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const text = await response.text();
                let data = {};
                try { data = JSON.parse(text); } catch(e) { data = { success: false, message: text || 'Invalid JSON response' }; }

                if (!response.ok && !data.success) {
                    console.error('Add to cart failed', response.status, data);
                    this.showNotification(data.message || (t.failed_to_add_to_cart || 'Failed to add to cart'), 'error');
                    return;
                }

                if (data.success) {
                    // determine variant label for user feedback from selected button
                    let variantLabel = null;
                    if (this.selectedVariantId) {
                        const btn = document.querySelector(`.variant-btn[data-variant-id="${this.selectedVariantId}"]`);
                        if (btn) {
                            const attrs = btn.getAttribute('data-attrs');
                            variantLabel = attrs ? attrs : (btn.getAttribute('data-sku') || null);
                        }
                    }
                    const what = variantLabel ? ` (${variantLabel})` : '';
                    this.showNotification((data.message || (t.added_to_cart || 'Product added to cart successfully!')) + what, 'success');
                    // Update cart counter by actual quantity added
                    try { if (Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', this.quantity); } catch(e){}
                } else {
                    this.showNotification(data.message || (t.error_adding_to_cart || 'Error adding product to cart'), 'error');
                }
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.showNotification(t.error_adding_to_cart || 'Error adding product to cart', 'error');
            } finally {
                this.loading = false;
            }
        },
        async addToWishlist(productId = null) {
                if (!productId) productId = this.productId;
                const t = window.productTranslations || {};
                this.loading = true;
                try {
                    const response = await fetch(`/wishlist/add/${productId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ variant_id: this.selectedVariantId })
                    });

                    const data = await response.json();

                    // determine variant label from selected button
                    let variantLabel = null;
                    const selBtn = document.querySelector(`.variant-btn[data-variant-id="${this.selectedVariantId}"]`);
                    if (selBtn) variantLabel = selBtn.getAttribute('data-attrs') || selBtn.getAttribute('data-sku');

                    if (data && data.success) {
                        const what = variantLabel ? ` (${variantLabel})` : '';
                        this.showNotification((data.message || (t.added_to_wishlist || 'Added to wishlist')) + what, 'success');
                        // Update wishlist counter
                        try { if (Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('wishlist', 1); } catch(e){}
                    } else {
                        this.showNotification(data.message || (t.failed_to_add_to_wishlist || 'Failed to add to wishlist'), 'error');
                    }
                } catch (err) {
                    console.error('addToWishlist error', err);
                    this.showNotification(t.network_error || 'Network error', 'error');
                } finally {
                    this.loading = false;
                }
            },
        
        // Alpine toast helpers
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
    }));
    
    // Register Recently Viewed component
    Alpine.data('rvComponent', () => {
        const KEY = 'rvp';
        const CURRENT_ID = window.currentProductId || 0;
        const CURRENT_PRODUCT = window.currentProductData || null;
        
        return {
            products: [],
            
            init() {
                if (!CURRENT_PRODUCT) return;
                
                let arr = [];
                try { 
                    arr = JSON.parse(localStorage.getItem(KEY) || '[]'); 
                } catch(e) { 
                    arr = []; 
                }
                
                // Show products except current one
                this.products = arr.filter(x => x.id !== CURRENT_ID).slice(0, 4);
                
                // Save current product
                arr = arr.filter(x => x.id !== CURRENT_ID);
                arr.unshift(CURRENT_PRODUCT);
                arr = arr.slice(0, 10);
                localStorage.setItem(KEY, JSON.stringify(arr));
            }
        };
    });
    
    productPageRegistered = true;
    return true;
}

// Try to register immediately if Alpine is already loaded
if (typeof Alpine !== 'undefined') {
    registerProductPageComponent();
}

// Also register on alpine:init in case Alpine loads later
document.addEventListener('alpine:init', () => {
    registerProductPageComponent();
});

// Note: DOM fallback removed. All toasts are now dispatched via `app:toast` CustomEvent

// Thumbnail gallery handler - must work immediately
function initThumbnails() {
    const thumbs = document.querySelectorAll('.thumb-btn');
    if (thumbs.length === 0) return;
    
    thumbs.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const src = this.getAttribute('data-thumb-src');
            if (!src) return;
            
            const mainImage = document.getElementById('main-product-image');
            if (mainImage) {
                mainImage.src = src;
            }
            
            // Update active state
            thumbs.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        }, { once: false });
    });
}

// Initialize as soon as possible
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThumbnails);
} else {
    initThumbnails();
}

// Fallback DOM handlers so UI works even if Alpine isn't ready
document.addEventListener('DOMContentLoaded', () => {
    // Try again after DOM is ready in case Alpine was loaded late
    if (typeof Alpine !== 'undefined') {
        registerProductPageComponent();
    }
    
    // ensure initial quantity visible and set to 1 if missing/invalid
    let qtyInput = document.getElementById('product-quantity');
    if (qtyInput) {
        const current = Number(qtyInput.value);
        if (!current || current < 1) {
            qtyInput.value = '1';
            qtyInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

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

    // Fallback Add to Cart (works without Alpine) - ONLY if Alpine not handling
    document.querySelectorAll('[data-add-to-cart]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            // Skip if Alpine is handling this (check if productPage component is active)
            if (productPageRegistered && typeof Alpine !== 'undefined') {
                // Alpine will handle it via @click
                return;
            }
            
            const productId = btn.getAttribute('data-product-id');
            const qtyEl = document.getElementById('product-quantity');
            const qty = Number(qtyEl ? qtyEl.value : 1) || 1;
            const maxAttr = btn.getAttribute('data-max');
            const maxAvail = (maxAttr !== null) ? Number(maxAttr) : Infinity;
            const t = window.productTranslations || {};

            // check stock before sending
                if (Number(qty) > Number(maxAvail)) {
                    const msg = (t.requested_qty_not_available || 'Requested quantity (:requested) not available. Only :available in stock.')
                        .replace(':requested', qty).replace(':available', maxAvail);
                    window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: msg, type: 'error' } }));
                return;
            }

            // simple UI feedback
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.textContent = t.adding || 'Adding...';

            try {
                // include variant_id if present in select
                const variantSelect = document.getElementById('product-variant');
                const variantId = variantSelect ? variantSelect.value || null : null;

                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ quantity: qty, variant_id: variantId })
                });
                const data = await response.json();

                if (data && data.success) {
                        window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: data.message || (t.added_to_cart || 'Added to cart'), type: 'success' } }));
                    // update global Alpine store if present
                    try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('cart', qty); } catch(e){}
                } else {
                        window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: (data && data.message) || (t.failed_to_add_to_cart || 'Failed to add to cart'), type: 'error' } }));
                }
            } catch (err) {
                console.error('Fallback addToCart error', err);
                    window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: t.network_error || 'Network error', type: 'error' } }));
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });

    // Fallback Add to Wishlist (works without Alpine) - ONLY if Alpine not handling
    document.querySelectorAll('[data-add-to-wishlist]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            // Skip if Alpine is handling this
            if (productPageRegistered && typeof Alpine !== 'undefined') {
                // Alpine will handle it via @click
                return;
            }
            
            const productId = btn.getAttribute('data-product-id');
            // include variant_id if present in select
            const variantSelect = document.getElementById('product-variant');
            const variantId = variantSelect ? variantSelect.value || null : null;
            const t = window.productTranslations || {};

            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.textContent = t.adding || 'Adding...';

            try {
                const response = await fetch(`/wishlist/add/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ variant_id: variantId })
                });

                const data = await response.json();

                // get human label for variant
                let variantLabel = null;
                if (variantSelect && variantSelect.value) {
                    const opt = variantSelect.querySelector(`option[value="${variantSelect.value}"]`);
                    if (opt) variantLabel = opt.textContent.trim();
                }

                if (data && data.success) {
                    const what = variantLabel ? ` (${variantLabel})` : '';
                        window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: (data.message || (t.added_to_wishlist || 'Added to wishlist')) + what, type: 'success' } }));
                    // update global Alpine store if present
                    try { if (window.Alpine && Alpine.store && Alpine.store('global')) Alpine.store('global').increment('wishlist', 1); } catch(e){}
                } else {
                        window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: (data && data.message) || (t.failed_to_add_to_wishlist || 'Failed to add to wishlist'), type: 'error' } }));
                }
            } catch (err) {
                console.error('Fallback addToWishlist error', err);
                    window.dispatchEvent(new CustomEvent('app:toast', { detail: { message: t.network_error || 'Network error', type: 'error' } }));
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
});