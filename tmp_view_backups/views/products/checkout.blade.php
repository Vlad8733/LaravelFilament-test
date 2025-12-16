@extends('layouts.app')

@section('content')
<div class="site-wrapper">
<!DOCTYPE html>
<html lang="en" x-data="checkout()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">

    <header class="mb-10 text-center">
        <h1 class="text-4xl font-bold text-gray-800">Checkout</h1>
        <p class="text-gray-600 mt-2">Review your order and enter your details</p>
    </header>

    <main class="max-w-3xl mx-auto">
        @if(empty($cart))
            <div class="bg-white p-6 rounded-xl shadow-md text-center">
                <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
                <a href="{{ route('products.index') }}" class="btn-primary">
                    Continue Shopping
                </a>
            </div>
        @else
            <!-- Order Summary -->
            <section class="bg-white p-6 rounded-xl shadow-md mb-6">
                <h2 class="text-2xl font-semibold mb-4">Order Summary</h2>
                
                @foreach($cart as $item)
                    <div class="flex justify-between items-center mb-2 py-2 border-b border-gray-100">
                        <div>
                            <p class="font-semibold">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }} × ${{ number_format($item['price'], 2) }}</p>
                        </div>
                        <p class="font-bold">${{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                    </div>
                @endforeach

                <div class="mt-4 pt-4 border-t flex justify-between font-bold text-lg">
                    <span>Total:</span>
                    <span>${{ number_format(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart)), 2) }}</span>
                </div>
            </section>

            <!-- Customer Details -->
            <section class="bg-white p-6 rounded-xl shadow-md mb-6">
                <h2 class="text-2xl font-semibold mb-4">Your Details</h2>
                <form action="{{ route('checkout.placeOrder') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-medium mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="input-field">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="input-field">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Shipping Address</label>
                        <textarea name="address" required class="input-field" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Method Selection -->
                    <div>
                        <label class="block font-medium mb-3">Payment Method</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="fake" class="mr-3" checked>
                                <div>
                                    <div class="font-semibold">Demo Payment</div>
                                    <div class="text-sm text-gray-500">For testing purposes</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="stripe" class="mr-3">
                                <div>
                                    <div class="font-semibold">Credit Card</div>
                                    <div class="text-sm text-gray-500">Via Stripe</div>
                                </div>
                            </label>

                            <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="paypal" class="mr-3">
                                <div>
                                    <div class="font-semibold">PayPal</div>
                                    <div class="text-sm text-gray-500">Secure payment</div>
                                </div>
                            </label>
                        </div>
                        @error('payment_method')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @error('payment')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full btn-success text-lg py-3">
                        Place Order - ${{ number_format(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart)), 2) }}
                    </button>
                </form>
            </section>

            @if(session('orderPlaced'))
                <div class="bg-green-50 border border-green-200 p-6 rounded-xl text-center">
                    <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-green-800 mb-2">Order Placed Successfully!</h3>
                    <p class="text-green-700">
                        Thank you, {{ session('name') }}! <br>
                        Order ID: <strong>{{ session('orderId') }}</strong><br>
                        We will contact you at {{ session('email') }}.
                    </p>
                    <a href="{{ route('products.index') }}" class="btn-primary mt-4 inline-block">
                        Continue Shopping
                    </a>
                </div>
            @endif
        @endif
    </main>

    <script>
        function checkout() {
            return {
                // Добавим логику для валидации формы если нужно
            }
        }
    </script>

</body>
</html>
</div>
@endsection
