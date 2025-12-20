@extends('layouts.app')

@section('title','Shopping Cart')

@push('styles')
    @vite('resources/css/cart/cartindex.css')
@endpush

@push('scripts')
    @vite('resources/js/cart/cartindex.js')
@endpush

@section('content')
    <div x-data="cartPage()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach($cartItems as $item)
                            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center space-x-4">
                                @if($item->product->getPrimaryImage())
                                    <img src="{{ asset('storage/' . $item->product->getPrimaryImage()->image_path) }}" 
                                         alt="{{ $item->product->name }}"
                                         class="w-20 h-20 object-cover rounded">
                                @else
                                    <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $item->product->name }}</h3>
                                    <p class="text-gray-600">${{ number_format($item->product->getCurrentPrice(), 2) }}</p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <button @click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                            class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center">{{ $item->quantity }}</span>
                                    <button @click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                            class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold">${{ number_format($item->product->getCurrentPrice() * $item->quantity, 2) }}</p>
                                    <button @click="removeItem({{ $item->id }})" 
                                            class="text-red-500 hover:text-red-700 text-sm">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-white rounded-lg shadow-sm p-6 h-fit">
                        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>${{ number_format($subtotal, 2) }}</span>
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                            <div class="flex space-x-2">
                                <input type="text" x-model="couponCode" 
                                       class="flex-1 min-w-0 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="XXXX-XXXX">
                                <button @click="applyCoupon()" 
                                        class="flex-none bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <a href="{{ route('checkout.show') }}" 
                           class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors block">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Inline script для регистрации Alpine data ДО его инициализации --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('cartPage', () => ({
                couponCode: '',

                updateQuantity(itemId, quantity) {
                    if (quantity < 1) return this.removeItem(itemId);
                    
                    fetch(`/cart/update/${itemId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ quantity })
                    })
                    .then(response => {
                        if (response.ok) location.reload();
                    })
                    .catch(error => console.error('Error:', error));
                },

                removeItem(itemId) {
                    fetch(`/cart/remove/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => {
                        if (response.ok) location.reload();
                    })
                    .catch(error => console.error('Error:', error));
                },

                applyCoupon() {
                    if (!this.couponCode.trim()) return;
                    
                    fetch('/cart/coupon/apply', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ code: this.couponCode })
                    })
                    .then(response => {
                        if (response.ok) location.reload();
                        else response.json().then(data => alert(data.message || 'Invalid coupon'));
                    })
                    .catch(error => console.error('Error:', error));
                }
            }));
        });
    </script>
@endsection