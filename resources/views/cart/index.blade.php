@extends('layouts.app')

@section('title','Shopping Cart')

@push('styles')
    {{-- подключаем CSS в head через стек --}}
    @vite('resources/css/cart/cartindex.css')
@endpush

@push('scripts')
    {{-- подключаем JS в конец body через стек --}}
    @vite('resources/js/cart/cartindex.js')
@endpush

@section('content')
    <div x-data="cartPage()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

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
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2 space-y-4">
                        @foreach($cart as $item)
                            <div class="bg-white rounded-lg shadow-sm p-6 flex items-center space-x-4">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" 
                                         alt="{{ $item['name'] }}"
                                         class="w-20 h-20 object-cover rounded">
                                @else
                                    <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center">
                                        <!-- placeholder -->
                                    </div>
                                @endif

                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                    <p class="text-gray-600">${{ number_format($item['price'], 2) }}</p>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <button @click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                            class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center">{{ $item['quantity'] }}</span>
                                    <button @click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                            class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="text-right">
                                    <p class="font-semibold">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                                    <button @click="removeItem({{ $item['id'] }})" 
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
                                <span>${{ number_format($cartTotal, 2) }}</span>
                            </div>
                            
                            @if($discount > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Discount:</span>
                                    <span>-${{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            
                            <hr class="my-3">
                            
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total:</span>
                                <span>${{ number_format($finalTotal, 2) }}</span>
                            </div>
                        </div>

                        <!-- Coupon Code -->
                        @if(empty($coupon))
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
                         @else
                             <div class="mb-6 p-3 bg-green-50 rounded-lg">
                                 <div class="flex justify-between items-center">
                                     <span class="text-green-700">Coupon: {{ $coupon['code'] }}</span>
                                     <button @click="removeCoupon()" class="text-red-500 hover:text-red-700">Remove</button>
                                 </div>
                             </div>
                         @endif

                        <a href="{{ route('checkout.show') }}" 
                           class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors block">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection