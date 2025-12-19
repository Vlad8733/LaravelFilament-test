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
            if (this.processing) return;

            if (this.paymentMethod === 'card') {
                if (!this.cardNumber || !this.cardExpiry || !this.cardCvv || !this.cardName) {
                    alert('Please fill in all card details');
                    return;
                }
            }

            this.processing = true;

            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('name', this.customerName);
                formData.append('email', this.customerEmail);
                formData.append('address', this.shippingAddress);
                formData.append('notes', this.notes || '');
                formData.append('payment_method', 'fake');

                const response = await fetch('/checkout', {
                    method: 'POST',
                    body: formData
                });

                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    const data = await response.json();
                    if (!data.success) {
                        alert(data.message || 'Payment failed');
                    }
                }
            } catch (error) {
                console.error('Checkout error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.processing = false;
            }
        }
    };
}

// Экспортируем в глобальную область для Alpine
window.checkout = checkout;

console.log('Checkout JS loaded');