@extends('layouts.app')

@section('content')
<div class="site-wrapper">
<!DOCTYPE html>
<html lang="en" x-data="cartPage()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">

    <!-- Notification -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         class="fixed top-4 right-4 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <span x-text="notification.message"></span>
    </div>

    <header class="mb-10 text-center">
        <h1 class="text-4xl font-bold text-gray-800">Shopping Cart</h1>
        <p class="text-gray-600 mt-2">Review your items</p>
    </header>

    <main class="max-w-4xl mx-auto">
        <div class="bg-white p-6 rounded-xl shadow-md">
            <template x-if="cart.length === 0">
                <div class="text-center py-8">
                    <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p class="text-xl text-gray-500 mb-4">Your cart is empty</p>
                    <a href="{{ route('products.index') }}" class="btn-primary">
                        Continue Shopping
                    </a>
                </div>
            </template>

            <template x-if="cart.length > 0">
                <div>
                    <div class="space-y-4">
                        <template x-for="item in cart" :key="item.id">
                            <div class="flex justify-between items-center border-b pb-4 bg-gray-50 p-4 rounded-lg">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold" x-text="item.name"></h3>
                                    <p class="text-gray-600" x-text="`$${parseFloat(item.price).toFixed(2)} each`"></p>
                                </div>
                                
                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center border rounded-lg">
                                        <button @click="updateQuantity(item.id, 'decrease')" 
                                                class="px-3 py-1 hover:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <span class="px-4 py-1 bg-gray-100" x-text="item.quantity"></span>
                                        <button @click="updateQuantity(item.id, 'increase')" 
                                                class="px-3 py-1 hover:bg-gray-200 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <span class="font-bold min-w-[80px] text-right" x-text="`$${(item.price * item.quantity).toFixed(2)}`"></span>
                                    
                                    <button @click="removeItem(item.id)" 
                                            class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 border-t pt-4">
                        <div class="flex justify-between items-center text-xl font-bold">
                            <span>Total:</span>
                            <span x-text="`$${cartTotal.toFixed(2)}`"></span>
                        </div>
                        
                        <div class="mt-6 flex justify-between">
                            <a href="{{ route('products.index') }}" class="btn-secondary">
                                Continue Shopping
                            </a>
                            <a href="{{ route('checkout.show') }}" class="btn-success">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </main>

    <script>
        function cartPage() {
            return {
                cart: {{ Js::from(array_values($cart)) }},
                notification: {
                    show: false,
                    message: ''
                },

                get cartTotal() {
                    return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
                },

                async updateQuantity(productId, action) {
                    try {
                        const response = await fetch(`/cart/update/${productId}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ action })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.cart = data.cart;
                            this.showNotification('Cart updated successfully');
                        }
                    } catch (error) {
                        console.error('Error updating cart:', error);
                        this.showNotification('Error updating cart', 'error');
                    }
                },

                async removeItem(productId) {
                    try {
                        const response = await fetch(`/cart/remove/${productId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.cart = data.cart;
                            this.showNotification('Item removed from cart');
                        }
                    } catch (error) {
                        console.error('Error removing item:', error);
                        this.showNotification('Error removing item', 'error');
                    }
                },

                showNotification(message, type = 'success') {
                    this.notification.message = message;
                    this.notification.show = true;
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                }
            }
        }
    </script>

</body>
</html>
</div>
@endsection