<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">

    @stack('styles')

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('searchBox', () => ({
            query: '',
            results: [],
            showResults: false,
            debounceTimeout: null,
            debounceSearch() {
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = setTimeout(() => this.search(), 300);
            },
            search() {
                if (!this.query || this.query.length < 2) {
                    this.results = [];
                    return;
                }
                fetch('/search?query=' + encodeURIComponent(this.query))
                    .then(res => res.json())
                    .then(data => { this.results = Array.isArray(data) ? data : []; })
                    .catch(() => { this.results = []; });
            }
        }));
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>

    <script>
        window.initialCartCount = {{ $cartCount ?? 0 }};
        window.initialWishlistCount = {{ $wishlistCount ?? 0 }};
        document.addEventListener('alpine:init', () => {
            Alpine.store('global', {
                // counts shown в бейджах
                cartCount: 0,
                wishlistCount: 0,

                // increment unread / new items
                increment(type, n = 1) {
                    if (type === 'cart') this.cartCount = Number(this.cartCount || 0) + Number(n);
                    if (type === 'wishlist') this.wishlistCount = Number(this.wishlistCount || 0) + Number(n);
                },

                // mark viewed -> скрыть бейджы
                markViewed(type) {
                    if (type === 'cart') this.cartCount = 0;
                    if (type === 'wishlist') this.wishlistCount = 0;
                }
            });
        });
    </script>

    <style>
        :root{
            --nav-bg: linear-gradient(180deg,#1a1a1a,#141414);
            --nav-border: rgba(255,255,255,0.04);
            --nav-text: #e5e7eb;
            --muted: #9ca3af;
            --accent: #f59e0b;
        }
        .main-nav {
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
            padding: 10px 0;
        }

        /* new: container relative so we can absolute-position logo and actions */
        .nav-container { position: relative; }

        /* fix logo to the left and vertically center it inside the nav */
        .nav-container .brand {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 50;
        }

        /* layout for center area: leave space for fixed logo on the left
           padding-left equals approx logo width + gap so search sits nearer logo */
        .nav-container .nav-center {
            display: flex;
            align-items: center;
            height: 64px;
            padding-left: 6.5rem; /* adjust if you change logo size */
        }

        /* fix actions to the right and vertically center */
        .nav-actions {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .main-nav .brand a {
            color: var(--accent);
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
        }
        .main-nav .search-input {
            background: #0f0f0f;
            border: 1px solid rgba(255,255,255,0.04);
            color: var(--nav-text);
            border-radius: 12px;
            padding: .5rem .75rem .5rem 2.6rem;
            width: 100%;
        }
        .main-nav .search-input::placeholder { color: var(--muted); }
        .main-nav .icon-left {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.45);
        }

        .nav-actions a { color: var(--nav-text); }
        .nav-avatar { border: 1px solid rgba(255,255,255,0.04); }

        .badge-counter {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            border: 2px solid rgba(0,0,0,0.3);
        }

        .nav-item-wrap { position: relative; display: inline-block; padding: .25rem; }

        /* Force navbar username keep original color — highest priority */
        .main-nav .user-name,
        .main-nav .user-name * ,
        .nav-center .user-name,
        .nav-actions .user-name,
        .user-menu .user-name,
        .navbar .user-name,
        a.user-name {
            color: inherit !important;
            text-decoration: none !important;
        }

        .main-nav .user-name:hover,
        .nav-center .user-name:hover,
        .nav-actions .user-name:hover,
        .user-menu .user-name:hover,
        .navbar .user-name:hover,
        a.user-name:hover {
            color: inherit !important;
        }

        /* Ensure logo has no border/outline/shadow and renders as block */
        .logo-link, .logo-link img.logo-img, .brand .logo-img {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            -webkit-box-shadow: none !important;
            display: block !important; /* removes inline image whitespace */
            padding: 0 !important;
            margin: 0 !important;
        }
        /* If SVG stroke/pseudo-element is used */
        .logo-link svg, .logo-link svg * { stroke: none !important; fill: none !important; }
        .logo-link::before, .logo-link::after { display: none !important; content: none !important; }
    </style>
</head>
<body class="">
    <!-- Navigation -->
    <nav class="main-nav sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 nav-container">
            <div class="nav-center">
                 <!-- Logo -->
                 <div class="flex items-center brand">
                     <a href="{{ url('/products') }}" class="inline-block logo-link">
                         <img src="{{ asset('storage/logo/logoShopLy.png') }}" alt="ShopLy" class="h-20 md:h-14 w-auto object-contain logo-img" loading="lazy">
                     </a>
                 </div>
                <!-- Search (moved closer to logo) -->
                <div class="flex-1 max-w-lg ml-20 mr-8">
                     <div class="relative" x-data="searchBox()">
                         <svg class="icon-left w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                         </svg>
                         <input type="text"
                                x-model="query"
                                @input="debounceSearch"
                                @focus="showResults = true"
                                @click.away="showResults = false"
                                placeholder="Search products..."
                                class="search-input">
                         
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
                 <!-- Cart, Wishlist & Actions (fixed right) -->
                 <div class="nav-actions">
                     <!-- Wishlist -->
                     <a href="{{ route('wishlist.index') }}" class="nav-item-wrap" @click="$store.global.markViewed('wishlist')">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                         </svg>
                        <span x-show="$store.global.wishlistCount > 0" 
                              x-text="$store.global.wishlistCount" 
                              class="badge-counter"></span>
                     </a>
                     <!-- Cart -->
                     <a href="{{ route('cart.show') }}" class="nav-item-wrap" @click="$store.global.markViewed('cart')">
                         <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#9f9e9eff">
                             <path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/>
                         </svg>
                        <span x-show="$store.global.cartCount > 0" 
                              x-text="$store.global.cartCount" 
                              class="badge-counter"></span>
                     </a>
                     
                     <a href="{{ route('checkout.show') }}"
                        class="px-4 py-2 rounded-lg text-center font-medium transition-colors flex-none max-w-xs whitespace-nowrap"
                        style="background: #f59e0b; color: #071017;">
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
     @yield('content')

 </body>
 
 {{-- allow pages to push additional scripts --}}
 @stack('scripts')
 </html>
