@extends('layouts.app')

@section('title', 'Checkout - My Shop')

@push('styles')
    @vite('resources/css/checkout/checkoutindex.css')
@endpush

@push('scripts')
    @vite('resources/js/checkout/checkoutindex.js')
@endpush

@section('content')

<div class="checkout-page">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>

        @if($cartItems->isEmpty())
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
            <form id="checkoutForm">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                        
                        <div class="space-y-4">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                    <div class="flex items-center space-x-3">
                                        @if($item->product->images->first())
                                            <img src="{{ Storage::url($item->product->images->first()->image_path) }}" 
                                                 alt="{{ $item->product->name }}"
                                                 class="w-12 h-12 object-cover rounded">
                                        @else
                                            <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $item->product->name }}</h3>
                                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                                        </div>
                                    </div>
                                    <p class="font-semibold text-gray-900">${{ number_format($item->product->price * $item->quantity, 2) }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-200 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span>Subtotal:</span>
                                <span>${{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($discount > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>Discount:</span>
                                    <span>-${{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold pt-2 border-t">
                                <span>Total:</span>
                                <span>${{ number_format($total, 2) }}</span>
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
                                           id="customerName"
                                           placeholder="John Doe"
                                           required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" 
                                           id="customerEmail"
                                           placeholder="john@example.com"
                                           required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                <textarea id="shippingAddress"
                                          placeholder="123 Main St, Apt 4B"
                                          required 
                                          rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                <textarea id="notes"
                                          placeholder="Add delivery instructions..."
                                          rows="2" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            </div>

                            <button type="button" 
                                    onclick="openPaymentModal()"
                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors">
                                Place Order - ${{ number_format($total, 2) }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Payment Modal -->
            <div id="paymentModal" 
                 class="hidden fixed inset-0 z-50 overflow-y-auto">
                
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm transition-opacity"
                     onclick="closePaymentModal()"></div>

                <!-- Modal Content -->
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="payment-modal">
                        
                        <!-- Header -->
                        <div class="modal-header">
                            <h3 class="modal-title">Payment Information</h3>
                            <button onclick="closePaymentModal()" class="modal-close" type="button">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            <!-- Payment Method Selection -->
                            <div class="space-y-3 mb-6">
                                <label class="payment-option active">
                                    <input type="radio" name="paymentMethod" value="card" checked class="hidden">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <span class="font-medium">Credit / Debit Card</span>
                                    </div>
                                </label>
                            </div>

                            <!-- Card Form -->
                            <div class="space-y-4">
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
                                           id="cardNumber"
                                           placeholder="4242 4242 4242 4242"
                                           maxlength="19"
                                           class="form-input w-full">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Expiry -->
                                    <div>
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" 
                                               id="cardExpiry"
                                               placeholder="MM/YY"
                                               maxlength="5"
                                               class="form-input w-full">
                                    </div>

                                    <!-- CVV -->
                                    <div>
                                        <label class="form-label">CVV</label>
                                        <input type="text" 
                                               id="cardCvv"
                                               placeholder="123"
                                               maxlength="4"
                                               class="form-input w-full">
                                    </div>
                                </div>

                                <!-- Cardholder Name -->
                                <div>
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" 
                                           id="cardName"
                                           placeholder="John Doe"
                                           class="form-input w-full">
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button onclick="closePaymentModal()" 
                                    type="button"
                                    class="btn-secondary">
                                Cancel
                            </button>
                            <button onclick="submitOrder()" 
                                    type="button"
                                    id="payButton"
                                    class="btn-primary">
                                <span id="payButtonText">Pay ${{ number_format($total, 2) }}</span>
                                <span id="processingText" class="hidden">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
