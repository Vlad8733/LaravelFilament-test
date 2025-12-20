@extends("layouts.app")

@section('title', 'Products - My Shop')

@push('styles')
    @vite('resources/css/products/productindex.css')
@endpush

@push('scripts')
    @vite(['resources/js/products/productindex.js'])
@endpush

@section('content')
<div x-data="shop()">
    <!-- Toast Notifications Container -->
    <div class="toast-container">
        <template x-for="(notification, index) in notifications.slice().reverse()" :key="notification.id">
            <div x-show="notification.show" 
                 x-transition:enter="toast-enter"
                 x-transition:leave="toast-leave"
                 :class="{
                     'success': notification.type === 'success',
                     'error': notification.type === 'error',
                     'info': notification.type === 'info'
                 }"
                 class="toast-notification">
                <div class="toast-icon">
                    <svg x-show="notification.type === 'success'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg x-show="notification.type === 'error'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <svg x-show="notification.type === 'info'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="toast-content">
                    <div class="toast-product-name" x-text="notification.productName"></div>
                    <div class="toast-message" x-text="notification.message"></div>
                </div>
                <button @click="removeNotification(notification.id)" class="toast-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="toast-progress"></div>
            </div>
        </template>
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
            <!-- FILTER DRAWER -->
            <div x-show="showFilters" x-cloak x-transition class="filters-backdrop" @click="showFilters = false"></div>
            <aside x-show="showFilters" x-cloak x-transition:enter="transition transform duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
                   x-transition:leave="transition transform duration-180" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
                   class="filters-drawer" @keydown.escape.window="showFilters = false" @click.stop>
                <div class="p-4 h-full flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Filters</h3>
                        <button @click="showFilters = false" class="px-2 py-1 rounded bg-gray-800 text-white">Close</button>
                    </div>
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Categories</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="category" value="all" x-model="filters.category" class="mr-2">
                                <span class="text-sm">All Categories</span>
                            </label>
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <label class="flex items-center">
                                        <input type="radio" name="category" value="{{ $category->id }}" x-model="filters.category" class="mr-2">
                                        <span class="text-sm">{{ $category->name }}</span>
                                    </label>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="mb-6">
                        <h4 class="font-medium mb-3">Price Range</h4>
                        <div class="filter-row">
                            <label class="block text-sm">
                                Min: $
                                <input type="number" x-model.number="filters.priceMin" min="0" step="1" placeholder="100" class="ml-2 px-2 py-1 rounded bg-gray-900 border border-gray-700 text-white w-32">
                            </label>
                            <label class="block text-sm ml-4">
                                Max: $
                                <input type="number" x-model.number="filters.priceMax" min="0" step="1" placeholder="2000" class="ml-2 px-2 py-1 rounded bg-gray-900 border border-gray-700 text-white w-32">
                            </label>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="flex items-center"><input type="checkbox" x-model="filters.inStock" class="mr-2"> In Stock Only</label>
                        <label class="flex items-center mt-2"><input type="checkbox" x-model="filters.onSale" class="mr-2"> On Sale</label>
                    </div>
                    <div class="mt-auto space-y-2">
                        <button @click="applyFilters(); showFilters=false" :disabled="filterLoading"
                                class="w-full bg-blue-600 text-white py-2 rounded">Apply Filters</button>
                        <button @click="clearFilters(); showFilters=false" class="w-full clear-filters py-2 rounded">Clear All Filters</button>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="flex-1">
                <!-- Toolbar -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-4">
                        <button @click="showFilters = !showFilters" class="p-2 rounded bg-gray-800 text-white flex items-center gap-2" title="Filters">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="currentColor"><path d="M440-160q-17 0-28.5-11.5T400-200v-240L168-736q-15-20-4.5-42t36.5-22h560q26 0 36.5 22t-4.5 42L560-440v240q0 17-11.5 28.5T520-160h-80Zm40-308 198-252H282l198 252Zm0 0Z"/></svg>
                            <span class="hidden sm:inline text-sm">Filters</span>
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
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
                                @if($product->sale_price)
                                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        -{{ $product->getDiscountPercentage() }}%
                                    </div>
                                @endif
                                @if(!$product->isInStock())
                                    <div class="absolute top-2 left-12 bg-gray-500 text-white text-xs font-bold px-2 py-1 rounded">
                                        Out of Stock
                                    </div>
                                @endif
                                <button @click="toggleWishlist({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                        :class="isInWishlist({{ $product->id }}) ? 'active' : ''"
                                        class="products-wish absolute top-2 right-2"
                                        type="button">
                                    <svg class="wish-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-4">
                                <div class="mb-2">
                                    <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">
                                        <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    @if($product->category && is_object($product->category))
                                        <span class="text-sm text-gray-500">{{ $product->category->name }}</span>
                                    @endif
                                </div>
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
                                <button @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}')"
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
                                <button @click="toggleWishlist({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                        :class="isInWishlist({{ $product->id }}) ? 'active' : ''"
                                        class="products-wish absolute top-2 right-2"
                                        type="button">
                                    <svg class="wish-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                            </div>
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
                @if($products->hasPages())
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('shop', () => ({
        viewMode: 'grid',
        showFilters: false,
        cartCount: 0,
        wishlistCount: 0,
        wishlistItems: [],
        loading: false,
        filterLoading: false,
        notifications: [],
        notificationIdCounter: 0,
        filters: {
            category: new URLSearchParams(window.location.search).get('category') || 'all',
            priceMin: (() => { const v = new URLSearchParams(window.location.search).get('price_min'); return v !== null ? Number(v) : null; })(),
            priceMax: (() => { const v = new URLSearchParams(window.location.search).get('price_max'); return v !== null ? Number(v) : null; })(),
            inStock: Boolean(new URLSearchParams(window.location.search).get('in_stock')),
            onSale: Boolean(new URLSearchParams(window.location.search).get('on_sale')),
            sort: new URLSearchParams(window.location.search).get('sort') || ''
        },

        init() {
            this.updateCartCount();
            this.updateWishlistCount();
            this.loadWishlistItems();
        },

        isInWishlist(productId) {
            return this.wishlistItems.includes(Number(productId));
        },

        async loadWishlistItems() {
            try {
                const res = await fetch('/wishlist/items', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.wishlistItems = Array.isArray(data.items) ? data.items.map(i => Number(i)) : [];
                this.wishlistCount = data.count ?? this.wishlistItems.length;
            } catch (e) { console.warn('Failed loading wishlist', e); }
        },

        async toggleWishlist(productId, productName = 'Product') {
            const id = Number(productId);
            const already = this.isInWishlist(id);
            if (!already) this.wishlistItems.push(id);
            else this.wishlistItems = this.wishlistItems.filter(x => x !== id);

            try {
                const url = already ? `/wishlist/remove/${id}` : `/wishlist/add/${id}`;
                const resp = await fetch(url, {
                    method: already ? 'DELETE' : 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
                });
                const json = await resp.json();
                if (json.success) {
                    this.wishlistCount = json.wishlistCount ?? this.wishlistItems.length;
                    this.showNotification(already ? 'removed from wishlist' : 'added to wishlist', 'success', productName);
                } else {
                    if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                    else this.wishlistItems.push(id);
                    this.showNotification(json.message || 'Failed', 'error', productName);
                }
            } catch (err) {
                if (!already) this.wishlistItems = this.wishlistItems.filter(x => x !== id);
                else this.wishlistItems.push(id);
                this.showNotification('Network error', 'error', productName);
            }
        },

        showNotification(message, type = 'success', productName = '') {
            const id = ++this.notificationIdCounter;
            this.notifications.push({ id, message, type, productName, show: true });
            if (this.notifications.length > 5) this.removeNotification(this.notifications[0].id);
            setTimeout(() => this.removeNotification(id), 4000);
        },

        removeNotification(id) {
            const idx = this.notifications.findIndex(n => n.id === id);
            if (idx !== -1) {
                this.notifications[idx].show = false;
                setTimeout(() => { this.notifications = this.notifications.filter(n => n.id !== id); }, 500);
            }
        },

        async addToCart(productId, productName = 'Product') {
            this.loading = true;
            try {
                const response = await fetch(`/cart/add/${productId}`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ quantity: 1 })
                });
                const data = await response.json();
                if (data.success) {
                    this.cartCount = data.cartCount;
                    if (Alpine.store('global')) Alpine.store('global').cartCount = data.cartCount;
                    this.showNotification('added to cart', 'success', productName);
                } else {
                    this.showNotification(data.message || 'Failed', 'error', productName);
                }
            } catch (error) {
                this.showNotification('Error adding to cart', 'error', productName);
            } finally {
                this.loading = false;
            }
        },

        async updateCartCount() {
            try {
                const res = await fetch('/cart/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.cartCount = data.count;
            } catch (e) { console.error(e); }
        },

        async updateWishlistCount() {
            try {
                const res = await fetch('/wishlist/count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                this.wishlistCount = data.count;
            } catch (e) { console.error(e); }
        },

        applyFilters() {
            if (this.filterLoading) return;
            this.filterLoading = true;
            const params = new URLSearchParams();
            if (this.filters.category && this.filters.category !== 'all') params.set('category', this.filters.category);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            if (this.filters.priceMin !== null) params.set('price_min', this.filters.priceMin);
            if (this.filters.priceMax !== null) params.set('price_max', this.filters.priceMax);
            if (this.filters.inStock) params.set('in_stock', '1');
            if (this.filters.onSale) params.set('on_sale', '1');
            window.location.href = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        },

        clearFilters() {
            this.filters = { category: 'all', priceMin: null, priceMax: null, inStock: false, onSale: false, sort: '' };
            this.applyFilters();
        }
    }));
});
</script>
@endsection