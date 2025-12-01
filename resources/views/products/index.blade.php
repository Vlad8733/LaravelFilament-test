<!DOCTYPE html>
<html lang="en" x-data="shop()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - My Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">MyShop</a>
                </div>

                <!-- Search -->
                <div class="flex-1 max-w-lg mx-8">
                    <div class="relative" x-data="searchBox()">
                        <input type="text" 
                               x-model="query" 
                               @input="debounceSearch"
                               @focus="showResults = true"
                               @click.away="showResults = false"
                               placeholder="Search products..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>

                        <!-- Search Results -->
                        <div x-show="showResults && results.length > 0" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute top-full left-0 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-96 overflow-y-auto z-50">
                            <template x-for="product in results" :key="product.id">
                                <a :href="product.url" class="flex items-center p-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                    <img x-show="product.image" 
                                         :src="product.image" 
                                         :alt="product.name"
                                         class="w-12 h-12 object-cover rounded mr-3">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900" x-text="product.name"></div>
                                        <div class="text-green-600 font-semibold" x-text="`$${product.price}`"></div>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Cart, Wishlist & Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Wishlist -->
                    <a href="{{ route('wishlist.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span x-show="wishlistCount > 0" 
                              x-text="wishlistCount" 
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"></span>
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart.show') }}" class="relative p-2 text-gray-600 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/></svg>
                        <span x-show="cartCount > 0" 
                              x-text="cartCount" 
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"></span>
                    </a>
                    
                    <a href="{{ route('checkout.show') }}" 
                       class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </nav>

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
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Min Price: $<span x-text="filters.priceMin"></span></label>
                                <input type="range" 
                                       x-model="filters.priceMin" 
                                       min="0" 
                                       :max="filters.priceMax - 1"
                                       step="10"
                                       class="w-full">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Max Price: $<span x-text="filters.priceMax"></span></label>
                                <input type="range" 
                                       x-model="filters.priceMax" 
                                       :min="filters.priceMin + 1"
                                       max="{{ $stats['price_range']['max'] ?? 1000 }}"
                                       step="10"
                                       class="w-full">
                            </div>
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
                        <button @click="clearFilters()" class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded hover:bg-gray-200 transition-colors">
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
                                        <a href="{{ route('products.show', $product) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details
                                        </a>
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

    <script>
        function shop() {
            return {
                viewMode: 'grid',
                cartCount: 0,
                wishlistCount: 0,
                wishlistItems: [],
                loading: false,
                filterLoading: false,
                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                filters: {
                    category: new URLSearchParams(window.location.search).get('category') || 'all',
                    priceMin: parseInt(new URLSearchParams(window.location.search).get('price_min')) || {{ $stats['price_range']['min'] ?? 0 }},
                    priceMax: parseInt(new URLSearchParams(window.location.search).get('price_max')) || {{ $stats['price_range']['max'] ?? 1000 }},
                    inStock: Boolean(new URLSearchParams(window.location.search).get('in_stock')),
                    onSale: Boolean(new URLSearchParams(window.location.search).get('on_sale')),
                    sort: new URLSearchParams(window.location.search).get('sort') || ''
                },

                init() {
                    this.updateCartCount();
                    this.updateWishlistCount();
                    this.loadWishlistItems();
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
                            body: JSON.stringify({
                                quantity: 1
                            })
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.cartCount = data.cartCount;
                            this.showNotification(data.message, 'success');
                        } else {
                            this.showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                        this.showNotification('Error adding product to cart', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async toggleWishlist(productId) {
                    try {
                        const isInWishlist = this.wishlistItems.includes(productId);
                        const url = isInWishlist ? `/wishlist/remove/${productId}` : `/wishlist/add/${productId}`;
                        const method = isInWishlist ? 'DELETE' : 'POST';

                        const response = await fetch(url, {
                            method: method,
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            this.wishlistCount = data.wishlistCount;
                            
                            if (isInWishlist) {
                                this.wishlistItems = this.wishlistItems.filter(id => id !== productId);
                            } else {
                                this.wishlistItems.push(productId);
                            }
                            
                            this.showNotification(data.message, 'success');
                        } else {
                            this.showNotification(data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error toggling wishlist:', error);
                        this.showNotification('Error updating wishlist', 'error');
                    }
                },

                async updateCartCount() {
                    try {
                        const response = await fetch('/cart/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        this.cartCount = data.count;
                    } catch (error) {
                        console.error('Error fetching cart count:', error);
                    }
                },

                async updateWishlistCount() {
                    try {
                        const response = await fetch('/wishlist/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        this.wishlistCount = data.count;
                    } catch (error) {
                        console.error('Error fetching wishlist count:', error);
                    }
                },

                async loadWishlistItems() {
                    try {
                        const response = await fetch('/wishlist/items', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        const data = await response.json();
                        this.wishlistItems = data.items;
                    } catch (error) {
                        console.error('Error loading wishlist items:', error);
                    }
                },

                applyFilters() {
                    if (this.filterLoading) return;
                    this.filterLoading = true;
                    
                    const params = new URLSearchParams();
                    
                    if (this.filters.category !== 'all') {
                        params.set('category', this.filters.category);
                    }
                    if (this.filters.sort) {
                        params.set('sort', this.filters.sort);
                    }
                    if (this.filters.priceMin > {{ $stats['price_range']['min'] ?? 0 }}) {
                        params.set('price_min', this.filters.priceMin);
                    }
                    if (this.filters.priceMax < {{ $stats['price_range']['max'] ?? 1000 }}) {
                        params.set('price_max', this.filters.priceMax);
                    }
                    if (this.filters.inStock) {
                        params.set('in_stock', '1');
                    }
                    if (this.filters.onSale) {
                        params.set('on_sale', '1');
                    }

                    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.location.href = url;
                },

                clearFilters() {
                    this.filters = {
                        category: 'all',
                        priceMin: {{ $stats['price_range']['min'] ?? 0 }},
                        priceMax: {{ $stats['price_range']['max'] ?? 1000 }},
                        inStock: false,
                        onSale: false,
                        sort: ''
                    };
                    this.applyFilters();
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

        function searchBox() {
            return {
                query: '',
                results: [],
                showResults: false,
                searchTimeout: null,

                debounceSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.performSearch();
                    }, 300);
                },

                async performSearch() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }

                    try {
                        const response = await fetch(`/products/search?q=${encodeURIComponent(this.query)}`);
                        this.results = await response.json();
                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];
                    }
                }
            }
        }
    </script>

</body>
</html>
