<!DOCTYPE html>
<html lang="en" x-data="checkout()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - My Shop</title>
    <link href="/css/app.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('products.index') }}" class="text-2xl font-bold text-blue-600">MyShop</a>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800">Continue Shopping</a>
                    <a href="{{ route('cart.show') }}" class="text-blue-600 hover:text-blue-800">Back to Cart</a>
                </div>
            </div>
        </div>
    </nav>

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
                                    @if($item['image'])
                                        <img src="{{ asset('storage/' . $item['image']) }}" 
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
                    
                    <form action="{{ route('checkout.place') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                            <textarea name="address" required rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="fake" class="mr-3" checked>
                                    <div>
                                        <div class="font-medium">Demo Payment</div>
                                        <div class="text-sm text-gray-500">For testing purposes only</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="stripe" class="mr-3">
                                    <div>
                                        <div class="font-medium">Credit Card</div>
                                        <div class="text-sm text-gray-500">Secure payment via Stripe</div>
                                    </div>
                                </label>

                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="radio" name="payment_method" value="paypal" class="mr-3">
                                    <div>
                                        <div class="font-medium">PayPal</div>
                                        <div class="text-sm text-gray-500">Pay with your PayPal account</div>
                                    </div>
                                </label>
                            </div>
                            @error('payment_method')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg text-lg font-medium hover:bg-green-700 transition-colors">
                            Place Order - ${{ number_format($finalTotal, 2) }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <script>
        function checkout() {
            return {
                init() {
                    console.log('Checkout page loaded');
                }
            }
        }
    </script>

</body>
</html>