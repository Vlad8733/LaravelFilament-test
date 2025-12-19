@extends('layouts.app')

@section('title','Checkout - My Shop')

@push('styles')
    @vite('resources/css/checkout/checkoutindex.css')
@endpush

@push('scripts')
    @vite('resources/js/checkout/checkoutindex.js')
@endpush

@section('content')
<div x-data="checkout()" class="checkout-page">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

        @if(empty($cart))
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <h2 class="text-xl font-medium text-gray-500 mb-4">Your cart is empty</h2>
                <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    Continue Shopping
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                    
                    <div class="space-y-4">
                        @foreach($cart as $item)
                            <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                <div class="flex items-center space-x-3">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" 
                                             alt="{{ $item['name'] }}"
                                             class="w-12 h-12 object-cover rounded">
                                    @endif
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $item['name'] }}</h3>
                                        <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                    </div>
                                </div>
                                <p class="font-semibold text-gray-900">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Subtotal:</span>
                            <span>${{ number_format($cartTotal, 2) }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Discount:</span>
                                <span>-${{ number_format($discount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold pt-2 border-t">
                            <span>Total:</span>
                            <span>${{ number_format($finalTotal, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" 
                                       x-model="customerName" 
                                       placeholder="John Doe"
                                       required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" 
                                       x-model="customerEmail" 
                                       placeholder="john@example.com"
                                       required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                            <textarea x-model="shippingAddress" 
                                      placeholder="123 Main St, Apt 4B"
                                      required 
                                      rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea x-model="notes" 
                                      placeholder="Add delivery instructions..."
                                      rows="2" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <button type="button" 
                                @click="openPaymentModal()"
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors">
                            Place Order - ${{ number_format($finalTotal, 2) }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Modal -->
            <div x-show="showPaymentModal" 
                 x-cloak
                 @keydown.escape.window="showPaymentModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm transition-opacity"
                     @click="showPaymentModal = false"
                     x-show="showPaymentModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"></div>

                <!-- Modal Content -->
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="payment-modal"
                         @click.away="showPaymentModal = false"
                         x-show="showPaymentModal"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95">
                        
                        <!-- Header -->
                        <div class="modal-header">
                            <h3 class="modal-title">Payment Information</h3>
                            <button @click="showPaymentModal = false" class="modal-close">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <!-- Payment Method Selection -->
                            <div class="space-y-3 mb-6">
                                <label class="payment-option"
                                       :class="paymentMethod === 'card' ? 'active' : ''">
                                    <input type="radio" x-model="paymentMethod" value="card" class="hidden">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <span class="font-medium">Credit / Debit Card</span>
                                    </div>
                                </label>
                                
                                <label class="payment-option"
                                       :class="paymentMethod === 'paypal' ? 'active' : ''">
                                    <input type="radio" x-model="paymentMethod" value="paypal" class="hidden">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.067 8.478c.492.88.556 2.014.3 3.327-.74 3.806-3.276 5.12-6.514 5.12h-.5a.805.805 0 00-.794.68l-.04.22-.63 3.993-.028.15a.805.805 0 01-.793.68H8.943c-.3 0-.54-.24-.54-.54 0-.03 0-.06.01-.09l.85-5.39.03-.18a.805.805 0 01.794-.68h.5c3.238 0 5.774-1.314 6.514-5.12.256-1.313.192-2.447-.3-3.327a3.327 3.327 0 00-1.02-1.023c.734-.68 1.573-1.013 2.564-1.013.99 0 1.83.333 2.564 1.013.384.347.69.738.924 1.163z"></path>
                                        </svg>
                                        <span class="font-medium">PayPal</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Card Form -->
                            <div x-show="paymentMethod === 'card'" x-transition class="space-y-4">
                                <!-- Demo Warning -->
                                <div class="demo-warning">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <strong>Demo Mode:</strong> Use test card 4242 4242 4242 4242
                                    </div>
                                </div>

                                <!-- Card Number -->
                                <div>
                                    <label class="form-label">Card Number</label>
                                    <input type="text" 
                                           x-model="cardNumber"
                                           @input="formatCardNumber"
                                           placeholder="4242 4242 4242 4242"
                                           maxlength="19"
                                           class="form-input w-full">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Expiry -->
                                    <div>
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" 
                                               x-model="cardExpiry"
                                               @input="formatExpiry"
                                               placeholder="MM/YY"
                                               maxlength="5"
                                               class="form-input w-full">
                                    </div>

                                    <!-- CVV -->
                                    <div>
                                        <label class="form-label">CVV</label>
                                        <input type="text" 
                                               x-model="cardCvv"
                                               @input="cardCvv = cardCvv.replace(/\D/g, '').slice(0, 4)"
                                               placeholder="123"
                                               maxlength="4"
                                               class="form-input w-full">
                                    </div>
                                </div>

                                <!-- Cardholder Name -->
                                <div>
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" 
                                           x-model="cardName"
                                           placeholder="John Doe"
                                           class="form-input w-full">
                                </div>
                            </div>

                            <!-- PayPal Note -->
                            <div x-show="paymentMethod === 'paypal'" x-transition class="demo-warning">
                                <p><strong>Demo Mode:</strong> PayPal integration is not active.</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button @click="showPaymentModal = false" 
                                    type="button"
                                    class="btn-secondary">
                                Cancel
                            </button>
                            <button @click="submitOrder()" 
                                    :disabled="processing"
                                    type="button"
                                    class="btn-primary">
                                <span x-show="!processing">Pay ${{ number_format($finalTotal, 2) }}</span>
                                <span x-show="processing">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection