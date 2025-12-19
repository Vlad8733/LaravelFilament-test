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
            console.log('Checkout initialized');
        },

        openPaymentModal() {
            console.log('Opening payment modal');
            
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
                console.log('Already processing, ignoring click');
                return;
            }

            if (this.paymentMethod === 'card') {
                if (!this.cardNumber || !this.cardExpiry || !this.cardCvv || !this.cardName) {
                    alert('Please fill in all card details');
                    return;
                }
            }

            this.processing = true;
            console.log('Starting order submission...');

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('name', this.customerName);
                formData.append('email', this.customerEmail);
                formData.append('address', this.shippingAddress);
                formData.append('notes', this.notes || '');
                formData.append('payment_method', 'fake');

                console.log('Sending request...');

                const response = await fetch('/checkout', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                console.log('Response status:', response.status);

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success && data.redirect) {
                    console.log('Order successful! Redirecting to:', data.redirect);
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
    console.log('✅ window.checkout registered:', typeof window.checkout);
}

// ГЛОБАЛЬНЫЕ ФУНКЦИИ ДЛЯ onclick:
window.openPaymentModal = function() {
    const name = document.getElementById('customerName').value;
    const email = document.getElementById('customerEmail').value;
    const address = document.getElementById('shippingAddress').value;
    
    if (!name || !email || !address) {
        alert('Please fill in all shipping information');
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
    
    const cardNumber = document.getElementById('cardNumber').value;
    const cardExpiry = document.getElementById('cardExpiry').value;
    const cardCvv = document.getElementById('cardCvv').value;
    const cardName = document.getElementById('cardName').value;
    
    if (!cardNumber || !cardExpiry || !cardCvv || !cardName) {
        alert('Please fill in all card details');
        return;
    }
    
    payButton.disabled = true;
    payButtonText.classList.add('hidden');
    processingText.classList.remove('hidden');
    
    try {
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('name', document.getElementById('customerName').value);
        formData.append('email', document.getElementById('customerEmail').value);
        formData.append('address', document.getElementById('shippingAddress').value);
        formData.append('notes', document.getElementById('notes').value || '');
        formData.append('payment_method', 'fake');
        
        console.log('📤 Sending order...');
        
        const response = await fetch('/checkout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });
        
        console.log('📥 Status:', response.status);
        
        const data = await response.json();
        console.log('✅ Data:', data);
        
        if (data.success && data.redirect) {
            console.log('🎉 SUCCESS! Redirecting...');
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

console.log('✅ Checkout JS loaded with global functions');