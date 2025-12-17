@extends('layouts.app')

@section('title','Products - My Shop')

@push('styles')
    @vite('resources/css/products/productindex.css')
@endpush

@push('scripts')
    @vite('resources/js/products/productindex.js')
@endpush

@section('content')
<div x-data="shop()" x-init="init()" x-cloak>
    <!-- Notification -->
    <div x-show="notification.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-full"
         :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'"
         class="fixed top-20 right-4 z-50 text-white px-6 py-3 rounded-lg shadow-lg max-w-sm">
        <div class="flex items-center">
            <svg x-show="notification.type === 'success'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <svg x-show="notification.type === 'error'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <span x-text="notification.message"></span>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="/" class="text-blue-600 hover:text-blue-800">Home</a></li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-900">Products</li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">All Products</h1>
            <p class="text-gray-600">Discover our amazing collection of {{ $stats['total_products'] ?? 0 }} products</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="w-full lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Categories</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="category" value="all" x-model="filters.category" class="mr-2">
                                <span class="text-sm">All Categories</span>
                                <span class="ml-auto text-xs text-gray-500">({{ $stats['total_products'] ?? 0 }})</span>
                            </label>
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <label class="flex items-center">
                                        <input type="radio" name="category" value="{{ $category->id }}" x-model="filters.category" class="mr-2">
                                        <span class="text-sm">{{ $category->name }}</span>
                                        <span class="ml-auto text-xs text-gray-500">({{ $category->products_count ?? 0 }})</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
                        <div class="filter-row">
                            <label class="block text-sm text-gray-300">
                                Min Price: $
                                <input type="number" x-model.number="filters.priceMin"
                                       min="0" step="1" placeholder="e.g. 100"
                                       class="ml-2 px-2 py-1 rounded bg-gray-900 border border-gray-700 text-white w-32">
                            </label>

                            <label class="block text-sm text-gray-300 ml-4">
                                Max Price: $
                                <input type="number" x-model.number="filters.priceMax"
                                       min="0" step="1" placeholder="e.g. 2000"
                                       class="ml-2 px-2 py-1 rounded bg-gray-900 border border-gray-700 text-white w-32">
                            </label>
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Availability</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="filters.inStock" class="mr-2">
                                <span class="text-sm">In Stock Only</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="filters.onSale" class="mr-2">
                                <span class="text-sm">On Sale</span>
                            </label>
                        </div>
                    </div>

                    <!-- Apply/Clear Buttons -->
                    <div class="space-y-2">
                        <button @click="applyFilters()" 
                                :disabled="filterLoading"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            <span x-show="!filterLoading">Apply Filters</span>
                            <span x-show="filterLoading">Loading...</span>
                        </button>
                        <button @click="clearFilters()" class="clear-filters w-full py-2 rounded">
                            Clear All Filters
                        </button>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Toolbar -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">
                            Showing {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                        </span>
                        
                        <!-- View Toggle -->
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button @click="viewMode = 'grid'" 
                                    :class="viewMode === 'grid' ? 'bg-white shadow-sm' : ''"
                                    class="p-2 rounded">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button @click="viewMode = 'list'" 
                                    :class="viewMode === 'list' ? 'bg-white shadow-sm' : ''"
                                    class="p-2 rounded">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Sort Options -->
                    <div class="flex items-center space-x-4">
                        <label class="text-sm text-gray-600">Sort by:</label>
                        <select x-model="filters.sort" @change="applyFilters()" class="border border-gray-300 rounded px-3 py-1 text-sm">
                            <option value="">Featured</option>
                            <option value="newest">Newest</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                            <option value="name_asc">Name: A to Z</option>
                            <option value="name_desc">Name: Z to A</option>
                            <option value="rating">Best Rating</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div :class="viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6' : 'space-y-4'">
                    @forelse($products as $product)
                        <!-- Grid View -->
                        <div x-show="viewMode === 'grid'" class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300 group">
                            <!-- Product Image -->
                            <div class="aspect-square bg-gray-200 relative overflow-hidden">
                                @if($product->getPrimaryImage())
                                    <img src="{{ asset('storage/' . $product->getPrimaryImage()->image_path) }}" 
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif

                                <!-- Sale Badge -->
                                @if($product->sale_price)
                                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        -{{ $product->getDiscountPercentage() }}%
                                    </div>
                                @endif

                                <!-- Stock Badge -->
                                @if(!$product->isInStock())
                                    <div class="absolute top-2 right-2 bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        Out of Stock
                                    </div>
                                @endif

                                <!-- Wishlist Button -->
                                <button @click="toggleWishlist({{ $product->id }})"
                                        :class="wishlistItems.includes({{ $product->id }}) ? 'text-red-500 bg-white' : 'text-gray-400 bg-white hover:text-red-500'"
                                        class="absolute top-2 right-2 w-8 h-8 rounded-full shadow-md flex items-center justify-center transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Product Info -->
                            <div class="p-4">
                                <div class="mb-2">
                                    <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">
                                        <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    
                                    <!-- Category -->
                                    @if($product->category && is_object($product->category))
                                        <span class="text-sm text-gray-500">{{ $product->category->name }}</span>
                                    @endif
                                </div>

                                <!-- Rating -->
                                <div class="flex items-center mb-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= round($product->getAverageRating()) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                 fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-500 ml-1">({{ $product->getReviewsCount() }})</span>
                                </div>

                                <!-- Price -->
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        @if($product->sale_price)
                                            <span class="text-lg font-bold text-green-600">${{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                        @else
                                            <span class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Add to Cart -->
                                <button @click="addToCart({{ $product->id }})" 
                                        :disabled="!{{ $product->isInStock() ? 'true' : 'false' }} || loading"
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                                    <span x-show="!loading">
                                        {{ $product->isInStock() ? 'Add to Cart' : 'Out of Stock' }}
                                    </span>
                                    <span x-show="loading">Adding...</span>
                                </button>
                            </div>
                        </div>

                        <!-- List View -->
                        <div x-show="viewMode === 'list'" class="bg-white rounded-lg shadow-sm p-6 flex space-x-6">
                            <!-- Product Image -->
                            <div class="w-32 h-32 bg-gray-200 rounded-lg flex-shrink-0 overflow-hidden relative">
                                @if($product->getPrimaryImage())
                                    <img src="{{ asset('storage/' . $product->getPrimaryImage()->image_path) }}" 
                                         alt="{{ $product->name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif

                                <!-- Wishlist Button for List View -->
                                <button @click="toggleWishlist({{ $product->id }})"
                                        :class="wishlistItems.includes({{ $product->id }}) ? 'text-red-500' : 'text-gray-400 hover:text-red-500'"
                                        class="absolute top-2 right-2 w-6 h-6 bg-white rounded-full shadow-md flex items-center justify-center transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Product Info -->
                            <div class="flex-1">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">
                                                {{ $product->name }}
                                            </a>
                                        </h3>
                                        @if($product->category && is_object($product->category))
                                            <span class="text-sm text-gray-500">{{ $product->category->name }}</span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-right">
                                        @if($product->sale_price)
                                            <div class="text-lg font-bold text-green-600">${{ number_format($product->sale_price, 2) }}</div>
                                            <div class="text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</div>
                                        @else
                                            <div class="text-lg font-bold text-gray-900">${{ number_format($product->price, 2) }}</div>
                                        @endif
                                    </div>
                                </div>

                                <p class="text-gray-600 text-sm mb-3 line-clamp-2">{{ $product->description }}</p>

                                <div class="flex items-center justify-between">
                                    <!-- Rating -->
                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= round($product->getAverageRating()) ? 'text-yellow-400' : 'text-gray-300' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-sm text-gray-500 ml-1">({{ $product->getReviewsCount() }})</span>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-3">
                                        <button @click="addToCart({{ $product->id }})" 
                                                :disabled="!{{ $product->isInStock() ? 'true' : 'false' }} || loading"
                                                class="bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors">
                                            {{ $product->isInStock() ? 'Add to Cart' : 'Out of Stock' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-16">
                            <svg class="w-24 h-24 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h3 class="text-xl font-medium text-gray-500 mb-2">No products found</h3>
                            <p class="text-gray-400 mb-4">Try adjusting your search or filter criteria</p>
                            <button @click="clearFilters()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Clear Filters
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection