@extends('layouts.app')

@section('content')
<div class="site-wrapper">
<!DOCTYPE html>
<html lang="en" x-data="productPage()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product->name }} - My Shop</title>
    <link href="/css/app.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Products
                    </a>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-600">{{ $product->category->name ?? 'Uncategorized' }}</span>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('cart.show') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        View Cart
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Product Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <!-- Product Images -->
            <div class="space-y-4">
                <div class="aspect-square bg-gray-200 rounded-lg overflow-hidden">
                    @if($product->images && $product->images->count() > 0)
                        @foreach($product->images as $index => $image)
                            <img x-show="selectedImage === {{ $index }}" 
                                 src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="{{ $image->alt_text ?? $product->name }}"
                                 class="w-full h-full object-cover">
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-32 h-32 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Thumbnail Images -->
                @if($product->images && $product->images->count() > 1)
                    <div class="flex space-x-2 overflow-x-auto">
                        @foreach($product->images as $index => $image)
                            <button @click="selectedImage = {{ $index }}"
                                    class="flex-shrink-0 w-20 h-20 bg-gray-200 rounded-lg overflow-hidden border-2 hover:border-blue-300 transition-colors"
                                    :class="selectedImage === {{ $index }} ? 'border-blue-500' : 'border-transparent'">
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     alt="{{ $image->alt_text ?? $product->name }}"
                                     class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="space-y-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                    
                    <!-- Rating -->
                    <div class="flex items-center mt-2">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= round($product->getAverageRating()) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="ml-2 text-sm text-gray-600">
                            {{ number_format($product->getAverageRating(), 1) }} 
                            ({{ $product->getReviewsCount() }} {{ Str::plural('review', $product->getReviewsCount()) }})
                        </span>
                    </div>
                </div>

                <!-- Price -->
                <div class="flex items-center space-x-3">
                    @if($product->sale_price)
                        <span class="text-3xl font-bold text-green-600">${{ number_format($product->sale_price, 2) }}</span>
                        <span class="text-xl text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                        <span class="bg-red-100 text-red-800 text-sm font-medium px-2.5 py-0.5 rounded">
                            {{ $product->getDiscountPercentage() }}% OFF
                        </span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="flex items-center">
                    @if($product->stock_quantity > 0)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $product->stock_quantity }} in stock
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Out of stock
                        </span>
                    @endif
                </div>

                <!-- Description -->
                <div>
                    <h3 class="text-lg font-semibold mb-2">Description</h3>
                    <p class="text-gray-600">{{ $product->description }}</p>
                </div>

                <!-- Long Description -->
                @if($product->long_description)
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Details</h3>
                        <div class="text-gray-600 prose max-w-none">
                            {!! nl2br(e($product->long_description)) !!}
                        </div>
                    </div>
                @endif

                <!-- Add to Cart -->
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700">Quantity:</label>
                        <div class="flex items-center border rounded-lg">
                            <button @click="quantity = Math.max(1, quantity - 1)" 
                                    class="px-3 py-2 hover:bg-gray-100 transition-colors">-</button>
                            <input type="number" x-model="quantity" min="1" :max="maxQuantity"
                                   class="w-16 text-center border-none focus:ring-0">
                            <button @click="quantity = Math.min(maxQuantity, quantity + 1)" 
                                    class="px-3 py-2 hover:bg-gray-100 transition-colors">+</button>
                        </div>
                    </div>

                    <button @click="addToCart()" 
                            :disabled="!canAddToCart || loading"
                            class="w-full bg-blue-600 text-white text-lg py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-show="!loading">Add to Cart</span>
                        <span x-show="loading">Adding...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h3 class="text-2xl font-semibold mb-6">Customer Reviews</h3>
            
            @if($product->reviews->count() > 0)
                <div class="space-y-6">
                    @foreach($product->reviews as $review)
                        <div class="border-b border-gray-200 pb-6 last:border-0">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ substr($review->reviewer_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $review->reviewer_name }}</h4>
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">{{ $review->created_at->format('M j, Y') }}</span>
                            </div>
                            <p class="mt-3 text-gray-600">{{ $review->comment }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No reviews yet. Be the first to review this product!</p>
            @endif
        </div>

        <!-- Related Products -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="mb-12">
                <h3 class="text-2xl font-semibold mb-6">Related Products</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <div class="aspect-square bg-gray-200">
                                @if($relatedProduct->getPrimaryImage())
                                    <img src="{{ asset('storage/' . $relatedProduct->getPrimaryImage()->image_path) }}" 
                                         alt="{{ $relatedProduct->name }}"
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="p-4">
                                <h4 class="font-semibold text-gray-900 mb-2">{{ $relatedProduct->name }}</h4>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-green-600">
                                        ${{ number_format($relatedProduct->getCurrentPrice(), 2) }}
                                    </span>
                                    <a href="{{ route('products.show', $relatedProduct) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Toast Notifications -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        function productPage() {
            return {
                selectedImage: 0,
                quantity: 1,
                maxQuantity: {{ $product->stock_quantity }},
                loading: false,

                get canAddToCart() {
                    return this.maxQuantity > 0 && this.quantity <= this.maxQuantity;
                },

                async addToCart() {
                    if (!this.canAddToCart) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch(`/cart/add/{{ $product->id }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                quantity: this.quantity
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            showToast('Product added to cart successfully!', 'success');
                        } else {
                            showToast(data.message || 'Error adding product to cart', 'error');
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        showToast('Error adding product to cart', 'error');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }

        // Toast notification function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            
            toast.innerHTML = `
                <div class="${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full">
                    ${message}
                </div>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.firstElementChild.classList.remove('translate-x-full');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.firstElementChild.classList.add('translate-x-full');
                setTimeout(() => {
                    if (container.contains(toast)) {
                        container.removeChild(toast);
                    }
                }, 300);
            }, 3000);
        }
    </script>

</body>
</html>
</div>
@endsection