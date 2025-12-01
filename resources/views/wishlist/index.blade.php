<!DOCTYPE html>
<html lang="en" x-data="wishlistPage()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - My Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800">Products</a>
                    <a href="{{ route('cart.show') }}" class="text-blue-600 hover:text-blue-800">Cart</a>
                    <a href="{{ route('wishlist.index') }}" class="text-blue-600 hover:text-blue-800">Wishlist</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800">Home</a></li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-900">Wishlist</li>
            </ol>
        </nav>

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
            <span class="text-gray-500">{{ $wishlistItems->count() }} items</span>
        </div>

        @if($wishlistItems->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($wishlistItems as $item)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                        <div class="aspect-square bg-gray-200 relative">
                            @if($item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                     alt="{{ $item->product->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <button @click="removeFromWishlist({{ $item->product->id }})"
                                    class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                <a href="{{ route('products.show', $item->product) }}" class="hover:text-blue-600">
                                    {{ $item->product->name }}
                                </a>
                            </h3>
                            
                            <p class="text-sm text-gray-500 mb-3">{{ $item->product->category->name ?? 'Uncategorized' }}</p>
                            
                            <div class="flex items-center justify-between mb-3">
                                @if($item->product->sale_price)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-green-600">${{ number_format($item->product->sale_price, 2) }}</span>
                                        <span class="text-sm text-gray-500 line-through">${{ number_format($item->product->price, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-lg font-bold text-gray-900">${{ number_format($item->product->price, 2) }}</span>
                                @endif
                                
                                @if($item->product->stock_quantity > 0)
                                    <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">In Stock</span>
                                @else
                                    <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded">Out of Stock</span>
                                @endif
                            </div>
                            
                            <div class="flex space-x-2">
                                <button @click="addToCart({{ $item->product->id }})"
                                        :disabled="{{ $item->product->stock_quantity > 0 ? 'false' : 'true' }} || loading"
                                        class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors text-sm">
                                    Add to Cart
                                </button>
                                
                                <a href="{{ route('products.show', $item->product) }}" 
                                   class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <h2 class="text-xl font-medium text-gray-500 mb-4">Your wishlist is empty</h2>
                <p class="text-gray-400 mb-6">Start adding products you love to keep track of them</p>
                <a href="{{ route('products.index') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    Browse Products
                </a>
            </div>
        @endif
    </div>

    <!-- Toast Notifications -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:leave="transition ease-in duration-200"
         :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed top-4 right-4 z-50 text-white px-6 py-3 rounded-lg shadow-lg">
        <span x-text="notification.message"></span>
    </div>

    <script>
        function wishlistPage() {
            return {
                loading: false,
                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                async removeFromWishlist(productId) {
                    try {
                        const response = await fetch(`/wishlist/remove/${productId}`, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            location.reload();
                        } else {
                            this.showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        this.showNotification('Error removing from wishlist', 'error');
                    }
                },

                async addToCart(productId) {
                    this.loading = true;
                    
                    try {
                        const response = await fetch(`/cart/add/${productId}`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ quantity: 1 })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.showNotification('Product added to cart!', 'success');
                        } else {
                            this.showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        this.showNotification('Error adding to cart', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                showNotification(message, type = 'success') {
                    this.notification.message = message;
                    this.notification.type = type;
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