function checkout() {
    return {
        // Customer fields
        customerName: '',
        customerEmail: '',
        shippingAddress: '',
        notes: '',
        
        // Payment fields
        showPaymentModal: false,
        paymentMethod: 'card',
        cardNumber: '',
        cardExpiry: '',
        cardCvv: '',
        cardName: '',
        
        processing: false,
        errors: {},

        init() {
            // Checkout initialized
        },

        openPaymentModal() {
            if (!this.customerName || !this.customerEmail || !this.shippingAddress) {
                alert('Please fill in all shipping information');
                return;
            }
            
            this.showPaymentModal = true;
        },

        formatCardNumber() {
            let value = this.cardNumber.replace(/\D/g, '');
            this.cardNumber = value.replace(/(\d{4})/g, '$1 ').trim();
        },

        formatExpiry() {
            let value = this.cardExpiry.replace(/\D/g, '');
            if (value.length >= 2) {
                this.cardExpiry = value.slice(0, 2) + '/' + value.slice(2, 4);
            } else {
                this.cardExpiry = value;
            }
        },

        async submitOrder() {
            if (this.processing) {
                return;
            }

            if (this.paymentMethod === 'card') {
                if (!this.cardNumber || !this.cardExpiry || !this.cardCvv || !this.cardName) {
                    alert('Please fill in all card details');
                    return;
                }
            }

            this.processing = true;

            try {
                const formData = new FormData();
                formData.append('_token', window.getCsrfToken());
                formData.append('name', this.customerName);
                formData.append('email', this.customerEmail);
                formData.append('address', this.shippingAddress);
                formData.append('notes', this.notes || '');
                formData.append('payment_method', 'fake');

                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.getCsrfToken()
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success && data.redirect) {
                    this.showPaymentModal = false;
                    window.location.href = data.redirect;
                } else {
                    console.error('Order failed:', data.message);
                    alert(data.message || 'Payment failed');
                    this.processing = false;
                }
            } catch (error) {
                console.error('Checkout error:', error);
                alert('An error occurred. Please try again.');
                this.processing = false;
            }
        }
    };
}

if (typeof window !== 'undefined') {
    window.checkout = checkout;
}

// ГЛОБАЛЬНЫЕ ФУНКЦИИ ДЛЯ onclick:
window.openPaymentModal = function() {
    const savedAddressRadio = document.querySelector('input[name="saved_address"]:checked');
    const newAddressForm = document.getElementById('newAddressForm');
    
    let name, email, address;
    
    if (savedAddressRadio && savedAddressRadio.value !== 'new') {
        // Using saved address
        name = savedAddressRadio.dataset.name;
        address = savedAddressRadio.dataset.address;
        email = document.getElementById('customerEmail').value;
    } else {
        // Using new address
        name = document.getElementById('customerName').value;
        email = document.getElementById('customerEmail').value;
        address = document.getElementById('shippingAddress').value;
        
        if (!name || !email || !address) {
            alert(window.checkoutTranslations?.fill_required || 'Please fill in all shipping information');
            return;
        }
    }
    
    if (!email) {
        alert(window.checkoutTranslations?.fill_required || 'Please fill in your email');
        return;
    }
    
    document.getElementById('paymentModal').classList.remove('hidden');
};

window.closePaymentModal = function() {
    document.getElementById('paymentModal').classList.add('hidden');
};

window.submitOrder = async function() {
    const payButton = document.getElementById('payButton');
    const payButtonText = document.getElementById('payButtonText');
    const processingText = document.getElementById('processingText');
    
    if (payButton.disabled) return;
    
    // Check if using saved payment or new card
    const savedPaymentRadio = document.querySelector('input[name="saved_payment"]:checked');
    const usingSavedPayment = savedPaymentRadio && savedPaymentRadio.value !== 'new';
    
    if (!usingSavedPayment) {
        const cardNumber = document.getElementById('cardNumber').value;
        const cardExpiry = document.getElementById('cardExpiry').value;
        const cardCvv = document.getElementById('cardCvv').value;
        const cardName = document.getElementById('cardName').value;
        
        if (!cardNumber || !cardExpiry || !cardCvv || !cardName) {
            alert(window.checkoutTranslations?.fill_card_details || 'Please fill in all card details');
            return;
        }
    }
    
    payButton.disabled = true;
    payButtonText.classList.add('hidden');
    processingText.classList.remove('hidden');
    
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
        // Get address info
        const savedAddressRadio = document.querySelector('input[name="saved_address"]:checked');
        if (savedAddressRadio && savedAddressRadio.value !== 'new') {
            formData.append('name', savedAddressRadio.dataset.name);
            formData.append('address', savedAddressRadio.dataset.address);
            formData.append('saved_address_id', savedAddressRadio.value);
        } else {
            formData.append('name', document.getElementById('customerName').value);
            formData.append('address', document.getElementById('shippingAddress').value);
        }
        
        formData.append('email', document.getElementById('customerEmail').value);
        formData.append('notes', document.getElementById('notes').value || '');
        formData.append('payment_method', 'fake');
        
        // Add saved payment method if used
        if (usingSavedPayment) {
            formData.append('saved_payment_id', savedPaymentRadio.value);
        }
        
        const response = await fetch('/checkout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.getCsrfToken()
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Payment failed');
            payButton.disabled = false;
            payButtonText.classList.remove('hidden');
            processingText.classList.add('hidden');
        }
    } catch (error) {
        console.error('❌ Error:', error);
        alert('An error occurred');
        payButton.disabled = false;
        payButtonText.classList.remove('hidden');
        processingText.classList.add('hidden');
    }
};

// Handle saved address selection
document.addEventListener('DOMContentLoaded', function() {
    const savedAddressInputs = document.querySelectorAll('input[name="saved_address"]');
    const newAddressForm = document.getElementById('newAddressForm');
    
    savedAddressInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Update selection styling
            document.querySelectorAll('.saved-address-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            this.closest('.saved-address-option').classList.add('selected');
            
            // Show/hide new address form
            if (newAddressForm) {
                if (this.value === 'new') {
                    newAddressForm.style.display = 'block';
                    document.getElementById('customerName').setAttribute('required', 'required');
                    document.getElementById('shippingAddress').setAttribute('required', 'required');
                } else {
                    newAddressForm.style.display = 'none';
                    document.getElementById('customerName').removeAttribute('required');
                    document.getElementById('shippingAddress').removeAttribute('required');
                }
            }
        });
    });
    
    // Handle saved payment selection
    const savedPaymentInputs = document.querySelectorAll('input[name="saved_payment"]');
    const newCardForm = document.getElementById('newCardForm');
    
    savedPaymentInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Update selection styling
            document.querySelectorAll('.saved-payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            this.closest('.saved-payment-option').classList.add('selected');
            
            // Show/hide new card form
            if (newCardForm) {
                if (this.value === 'new') {
                    newCardForm.style.display = 'block';
                } else {
                    newCardForm.style.display = 'none';
                }
            }
        });
    });
});

// Checkout JS loaded