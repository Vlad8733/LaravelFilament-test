<!-- Settings Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Panel Navigation
    const navItems = document.querySelectorAll('.settings-nav-item[data-panel]');
    const panels = document.querySelectorAll('.settings-panel');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.dataset.panel;
            
            navItems.forEach(i => i.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById('panel-' + target)?.classList.add('active');
            
            // Update URL hash
            history.replaceState(null, null, '#' + target);
        });
    });
    
    // Handle URL hash on load
    if (window.location.hash) {
        const target = window.location.hash.substring(1);
        const item = document.querySelector(`.settings-nav-item[data-panel="${target}"]`);
        if (item) item.click();
    }
    
    // Theme Selection
    const themeCards = document.querySelectorAll('.theme-card input[name="theme"]');
    const currentTheme = localStorage.getItem('theme') || 'auto';
    
    themeCards.forEach(input => {
        if (input.value === currentTheme) {
            input.checked = true;
            input.closest('.theme-card').classList.add('selected');
        }
        
        input.addEventListener('change', function() {
            themeCards.forEach(i => i.closest('.theme-card').classList.remove('selected'));
            this.closest('.theme-card').classList.add('selected');
            localStorage.setItem('theme', this.value);
            applyTheme(this.value);
            showToast('{{ __("settings.theme_saved") }}', 'success');
        });
    });
    
    function applyTheme(theme) {
        document.documentElement.classList.remove('theme-light', 'theme-dark');
        document.body.classList.remove('theme-light', 'theme-dark');
        
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const themeClass = prefersDark ? 'theme-dark' : 'theme-light';
            document.documentElement.classList.add(themeClass);
            document.body.classList.add(themeClass);
        } else {
            const themeClass = theme === 'light' ? 'theme-light' : 'theme-dark';
            document.documentElement.classList.add(themeClass);
            document.body.classList.add(themeClass);
        }
    }
    
    // Apply saved theme on page load
    applyTheme(currentTheme);
    
    // Password Form
    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route("settings.password.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.password_updated") }}', 'success');
                    this.reset();
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    }
    
    // Address Modal
    const addressModal = document.getElementById('address-modal');
    const addressForm = document.getElementById('address-form');
    const addressModalTitle = document.getElementById('address-modal-title');
    const firstAddressHint = document.getElementById('first-address-hint');
    const addressCheckbox = document.getElementById('address_is_default');
    const addressDefaultWarning = document.getElementById('default-address-warning');
    const hasAddresses = document.querySelectorAll('.address-card').length > 0;
    const addressCount = document.querySelectorAll('.address-card').length;
    let editingDefaultAddress = false;
    
    // Prevent unchecking default address if it's the only one
    if (addressCheckbox) {
        addressCheckbox.addEventListener('change', function() {
            if (editingDefaultAddress && !this.checked && addressCount <= 1) {
                this.checked = true;
                if (addressDefaultWarning) {
                    addressDefaultWarning.style.display = 'block';
                    setTimeout(() => {
                        addressDefaultWarning.style.display = 'none';
                    }, 5000);
                }
            }
        });
    }
    
    function openAddressModal(address = null) {
        // Hide warning on open
        if (addressDefaultWarning) addressDefaultWarning.style.display = 'none';
        
        const addressBtnText = document.getElementById('address-btn-text');
        
        if (address) {
            addressModalTitle.textContent = '{{ __('settings.edit_address') }}';
            if (addressBtnText) addressBtnText.textContent = '{{ __('settings.update_address') }}';
            document.getElementById('address_id').value = address.id;
            document.getElementById('address_label').value = address.label;
            document.getElementById('address_full_name').value = address.full_name;
            document.getElementById('address_phone').value = address.phone;
            document.getElementById('address_line1').value = address.address_line1 || address.address_line_1 || '';
            document.getElementById('address_line2').value = address.address_line2 || address.address_line_2 || '';
            document.getElementById('address_city').value = address.city;
            document.getElementById('address_state').value = address.state || '';
            document.getElementById('address_postal_code').value = address.postal_code;
            document.getElementById('address_country').value = address.country;
            document.getElementById('address_is_default').checked = address.is_default;
            editingDefaultAddress = address.is_default;
            // Hide hint when editing
            if (firstAddressHint) firstAddressHint.style.display = 'none';
            if (addressCheckbox) addressCheckbox.disabled = false;
        } else {
            addressModalTitle.textContent = '{{ __("settings.add_address") }}';            if (addressBtnText) addressBtnText.textContent = '{{ __('settings.save_address') }}';            addressForm.reset();
            document.getElementById('address_id').value = '';
            editingDefaultAddress = false;
            // Show hint and auto-check if first address
            if (!hasAddresses) {
                if (firstAddressHint) firstAddressHint.style.display = 'block';
                if (addressCheckbox) {
                    addressCheckbox.checked = true;
                    addressCheckbox.disabled = true;
                }
            } else {
                if (firstAddressHint) firstAddressHint.style.display = 'none';
                if (addressCheckbox) addressCheckbox.disabled = false;
            }
        }
        addressModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeAddressModal() {
        addressModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    document.getElementById('add-address-btn')?.addEventListener('click', () => openAddressModal());
    document.getElementById('add-first-address-btn')?.addEventListener('click', () => openAddressModal());
    document.getElementById('close-address-modal')?.addEventListener('click', closeAddressModal);
    document.getElementById('cancel-address-modal')?.addEventListener('click', closeAddressModal);
    addressModal?.querySelector('.settings-modal-backdrop')?.addEventListener('click', closeAddressModal);
    
    document.querySelectorAll('.edit-address').forEach(btn => {
        btn.addEventListener('click', function() {
            const address = JSON.parse(this.dataset.address);
            openAddressModal(address);
        });
    });
    
    // Address Form Submit
    addressForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const addressId = document.getElementById('address_id').value;
        const isEdit = !!addressId;
        
        // Add method override for PUT when editing
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        const saveBtn = document.getElementById('save-address-btn');
        saveBtn.classList.add('loading');
        
        try {
            const url = isEdit 
                ? `/settings/addresses/${addressId}`
                : '{{ route("settings.addresses.store") }}';
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok) {
                showToast(data.message || '{{ __("settings.address_saved") }}', 'success');
                closeAddressModal();
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
            }
        } catch (error) {
            showToast('{{ __("settings.error_occurred") }}', 'error');
        } finally {
            saveBtn.classList.remove('loading');
        }
    });
    
    // Delete Address
    document.querySelectorAll('.delete-address').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('{{ __("settings.confirm_delete_address") }}')) return;
            
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/addresses/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.address_deleted") }}', 'success');
                    this.closest('.address-card').remove();
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Set Default Address
    document.querySelectorAll('.set-default-address').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/addresses/${id}/default`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.default_address_set") }}', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Payment Modal
    const paymentModal = document.getElementById('payment-modal');
    const paymentForm = document.getElementById('payment-form');
    const paymentModalTitle = document.getElementById('payment-modal-title');
    const paymentDefaultRow = document.getElementById('payment-default-row');
    
    function openPaymentModal(payment = null) {
        paymentForm.reset();
        document.getElementById('payment_id').value = '';
        
        const cardNumberInput = document.getElementById('card_number');
        const cardNumberHint = document.getElementById('card-number-hint');
        const paymentBtnText = document.getElementById('payment-btn-text');
        
        if (payment) {
            // Edit mode
            paymentModalTitle.textContent = '{{ __('settings.edit_payment_method') }}';
            if (paymentBtnText) paymentBtnText.textContent = '{{ __('settings.update_card') }}';
            document.getElementById('payment_id').value = payment.id;
            cardNumberInput.value = '';
            cardNumberInput.placeholder = '{{ __("settings.leave_empty_to_keep") }} (**** ' + payment.last_four + ')';
            cardNumberInput.removeAttribute('required');
            document.getElementById('holder_name').value = payment.holder_name;
            document.getElementById('expiry_month').value = payment.expiry_month;
            document.getElementById('expiry_year').value = payment.expiry_year;
            document.getElementById('cvv').value = '';
            document.getElementById('cvv').removeAttribute('required');
            document.getElementById('payment_is_default').checked = payment.is_default;
            
            // Hide default checkbox if already default
            if (paymentDefaultRow) {
                paymentDefaultRow.style.display = payment.is_default ? 'none' : 'block';
            }
        } else {
            // Add mode
            paymentModalTitle.textContent = '{{ __("settings.add_payment_method") }}';            if (paymentBtnText) paymentBtnText.textContent = '{{ __('settings.add_card') }}';            cardNumberInput.placeholder = '1234 5678 9012 3456';
            cardNumberInput.setAttribute('required', 'required');
            document.getElementById('cvv').setAttribute('required', 'required');
            
            // Show default checkbox
            if (paymentDefaultRow) {
                paymentDefaultRow.style.display = 'block';
            }
        }
        
        paymentModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closePaymentModal() {
        paymentModal.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    document.getElementById('add-payment-btn')?.addEventListener('click', () => openPaymentModal());
    document.getElementById('add-first-payment-btn')?.addEventListener('click', () => openPaymentModal());
    document.getElementById('close-payment-modal')?.addEventListener('click', closePaymentModal);
    document.getElementById('cancel-payment-modal')?.addEventListener('click', closePaymentModal);
    paymentModal?.querySelector('.settings-modal-backdrop')?.addEventListener('click', closePaymentModal);
    
    // Edit Payment Method
    document.querySelectorAll('.edit-payment').forEach(btn => {
        btn.addEventListener('click', function() {
            const payment = JSON.parse(this.dataset.payment);
            openPaymentModal(payment);
        });
    });
    
    // Card Number Formatting
    const cardNumberInput = document.getElementById('card_number');
    const cardBrandIcon = document.getElementById('card-brand-icon');
    
    cardNumberInput?.addEventListener('input', function() {
        let value = this.value.replace(/\D/g, '');
        let formatted = '';
        
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += value[i];
        }
        
        this.value = formatted;
        
        // Detect card brand
        const brand = detectCardBrand(value);
        updateCardBrandIcon(brand);
    });
    
    function detectCardBrand(number) {
        if (/^4/.test(number)) return 'visa';
        if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) return 'mastercard';
        if (/^3[47]/.test(number)) return 'amex';
        return null;
    }
    
    function updateCardBrandIcon(brand) {
        if (!cardBrandIcon) return;
        
        if (brand === 'visa') {
            cardBrandIcon.innerHTML = '<svg viewBox="0 0 24 16" fill="#1A1F71"><text x="2" y="12" font-size="8" font-weight="bold">VISA</text></svg>';
        } else if (brand === 'mastercard') {
            cardBrandIcon.innerHTML = '<svg viewBox="0 0 24 16"><circle cx="8" cy="8" r="6" fill="#EB001B"/><circle cx="16" cy="8" r="6" fill="#F79E1B"/></svg>';
        } else {
            cardBrandIcon.innerHTML = '';
        }
    }
    
    // CVV Input
    document.getElementById('cvv')?.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').substring(0, 4);
    });
    
    // Payment Form Submit
    paymentForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const paymentId = document.getElementById('payment_id').value;
        const isEdit = !!paymentId;
        
        const cardNumber = formData.get('card_number')?.replace(/\s/g, '') || '';
        
        // Check if card number was provided
        if (cardNumber && cardNumber.length >= 4) {
            const lastFour = cardNumber.slice(-4);
            const brand = detectCardBrand(cardNumber);
            
            formData.append('type', 'card');
            formData.append('last_four', lastFour);
            if (brand) formData.append('brand', brand);
        } else if (!isEdit) {
            // New card requires valid card number
            showToast('{{ __("settings.enter_valid_card") }}', 'error');
            return;
        }
        
        if (isEdit) {
            formData.append('_method', 'PUT');
        } else {
            formData.append('type', 'card');
        }
        
        const saveBtn = document.getElementById('save-payment-btn');
        saveBtn.classList.add('loading');
        
        try {
            const url = isEdit 
                ? `/settings/payment-methods/${paymentId}`
                : '{{ route("settings.payment-methods.store") }}';
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (response.ok) {
                showToast(data.message || '{{ __("settings.payment_method_added") }}', 'success');
                closePaymentModal();
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
            }
        } catch (error) {
            showToast('{{ __("settings.error_occurred") }}', 'error');
        } finally {
            saveBtn.classList.remove('loading');
        }
    });
    
    // Delete Payment Method
    document.querySelectorAll('.delete-payment').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('{{ __("settings.confirm_delete_payment") }}')) return;
            
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/payment-methods/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.payment_method_deleted") }}', 'success');
                    this.closest('.payment-card').remove();
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Set Default Payment Method
    document.querySelectorAll('.set-default-payment').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/payment-methods/${id}/default`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.default_payment_set") }}', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Unlink Social Account
    document.querySelectorAll('.unlink-social').forEach(btn => {
        btn.addEventListener('click', async function() {
            const provider = this.dataset.provider;
            if (!confirm(`{{ __("settings.confirm_unlink_social") }} ${provider}?`)) return;
            
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/social-accounts/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.social_account_unlinked") }}', 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Unfollow Company
    document.querySelectorAll('.unfollow-company').forEach(btn => {
        btn.addEventListener('click', async function() {
            const name = this.dataset.name;
            if (!confirm(`{{ __("settings.confirm_unfollow") }} ${name}?`)) return;
            
            const id = this.dataset.id;
            
            try {
                const response = await fetch(`/settings/unfollow-company/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    showToast(data.message || '{{ __("settings.company_unfollowed") }}', 'success');
                    this.closest('.followed-company-card').remove();
                } else {
                    showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
                }
            } catch (error) {
                showToast('{{ __("settings.error_occurred") }}', 'error');
            }
        });
    });
    
    // Newsletter Toggle
    const newsletterToggle = document.getElementById('newsletter-toggle');
    newsletterToggle?.addEventListener('change', async function() {
        const subscribed = this.checked;
        
        try {
            const response = await fetch('{{ route("settings.newsletter.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ subscribed })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                const statusSpan = this.closest('.newsletter-toggle').querySelector('.toggle-status');
                statusSpan.textContent = subscribed 
                    ? '{{ __("settings.subscribed") }}' 
                    : '{{ __("settings.not_subscribed") }}';
                showToast(data.message, 'success');
            } else {
                this.checked = !subscribed;
                showToast(data.message || '{{ __("settings.error_occurred") }}', 'error');
            }
        } catch (error) {
            this.checked = !subscribed;
            showToast('{{ __("settings.error_occurred") }}', 'error');
        }
    });
    
    // Toast Notification
    function showToast(message, type = 'info') {
        const existing = document.querySelector('.settings-toast');
        if (existing) existing.remove();
        
        const toast = document.createElement('div');
        toast.className = `settings-toast settings-toast-${type}`;
        
        const icon = type === 'success' 
            ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            : type === 'error'
            ? '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            : '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        
        toast.innerHTML = `${icon}<span>${message}</span>`;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddressModal();
            closePaymentModal();
        }
    });
    
    // Copy Order Number
    document.querySelectorAll('.copy-order-number').forEach(btn => {
        btn.addEventListener('click', async function() {
            const orderNumber = this.dataset.number;
            try {
                await navigator.clipboard.writeText(orderNumber);
                showToast('{{ __("settings.copied") }}', 'success');
            } catch (err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = orderNumber;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showToast('{{ __("settings.copied") }}', 'success');
            }
        });
    });
});
</script>
