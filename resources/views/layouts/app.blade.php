<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name', 'MyShop') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ВСЁ КАК У ТЕБЯ БЫЛО --}}
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>

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

    {{-- ================= ТВОЙ NAV (НЕ ТРОНУТ) ================= --}}
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
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000">
                            <path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/>
                        </svg>
                        <span x-show="cartCount > 0" 
                              x-text="cartCount" 
                              class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"></span>
                    </a>
                    
                    <a href="{{ route('checkout.show') }}" 
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors flex-none max-w-xs whitespace-nowrap">
                        Proceed to Checkout
                    </a>

                    @auth
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-2 text-gray-600 hover:text-gray-900 flex-none">
                            <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(auth()->user()->email))) . '?s=40&d=identicon' }}" 
                                 alt="avatar" class="w-8 h-8 rounded-full object-cover nav-avatar border">
                            <span class="hidden sm:inline text-sm">{{ auth()->user()->name }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline">Sign in</a>
                    @endauth
                 </div>
            </div>
        </div>
    </nav>
    {{-- ================= /ТВОЙ NAV ================= --}}

    {{-- СЮДА ВСТАВЛЯЮТСЯ СТРАНИЦЫ --}}
    @yield('content')

</body>
<script>
function searchBox() {
    return {
        query: '',
        results: [],
        showResults: false,
        debounceTimeout: null,

        debounceSearch() {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.search();
            }, 300);
        },

        search() {
            if (this.query.length < 2) {
                this.results = [];
                return;
            }

            fetch('/search?query=' + encodeURIComponent(this.query))
                .then(res => res.json())
                .then(data => {
                    this.results = data;
                })
                .catch(() => {
                    this.results = [];
                });
        }
    }
}
</script>

</html>
