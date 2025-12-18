<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('styles')

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>

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
                        .then(data => this.results = Array.isArray(data) ? data : []);
                }
            }));

            Alpine.store('global', {
                cartCount: 0,
                wishlistCount: 0,
                markViewed(type) {
                    if (type === 'cart') this.cartCount = 0;
                    if (type === 'wishlist') this.wishlistCount = 0;
                }
            });
        });
    </script>

    <style>
        :root {
            --nav-h: 64px;
            --bg: #071017;
            --nav-bg: linear-gradient(180deg,#1a1a1a,#141414);
            --accent: #f59e0b;
        }

        html {
            overflow-y: scroll;
        }

        body {
            margin: 0;
            padding-top: var(--nav-h);
            background: var(--bg);
            color: #e5e7eb;
        }

        /* NAVBAR */
        #site-nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--nav-h);
            background: var(--nav-bg);
            border-bottom: 1px solid rgba(255,255,255,.05);
            z-index: 1000;
        }

        .nav-wrap {
            max-width: 1120px;
            margin: 0 auto;
            height: 100%;
            padding: 0 16px;
            display: grid;
            grid-template-columns: auto 1fr auto;
            align-items: center;
            gap: 16px;
        }

        /* LOGO */
        .logo-img {
            height: 40px;
            width: auto;
            display: block;
        }

        /* SEARCH */
        .search-input {
            width: 100%;
            height: 40px;
            padding-left: 2.6rem;
            border-radius: 10px;
            background: #0f0f0f;
            border: 1px solid rgba(255,255,255,.05);
            color: #e5e7eb;
        }

        .icon-left {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,.4);
        }

        /* ACTIONS */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-counter {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: #fff;
            width: 18px;
            height: 18px;
            font-size: 11px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .user-name { display: none; }
        }

        /* Force-logo safe rules: конкретно для логотипа в шапке, чтобы его не ломали глобальные img-правила */
        #site-nav .logo-img {
            width: auto !important;
            height: 40px !important;
            max-width: none !important;
            max-height: 40px !important;
            object-fit: contain !important;
            display: block !important;
            background: transparent !important;
            border: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* Блокируем глобальные правила для img только внутри navbar */
        #site-nav img {
            max-width: none !important;
            height: auto !important;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav id="site-nav">
        <div class="nav-wrap">
            <!-- LOGO -->
            <a href="{{ url('/products') }}">
                <img src="{{ asset('storage/logo/logoShopLy.png') }}" class="logo-img" alt="ShopLy">
            </a>

            <!-- SEARCH -->
            <div class="relative" x-data="searchBox()">
                <svg class="icon-left w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input class="search-input" placeholder="Search products…" x-model="query" @input="debounceSearch">
            </div>

            <!-- ACTIONS -->
            <div class="nav-actions">
                <a href="{{ route('wishlist.index') }}" class="nav-item-wrap" @click="$store.global.markViewed('wishlist')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span x-show="$store.global.wishlistCount" class="badge-counter" x-text="$store.global.wishlistCount"></span>
                </a>

                <a href="{{ route('cart.show') }}" class="nav-item-wrap" @click="$store.global.markViewed('cart')">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#9f9e9eff">
                        <path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/>
                    </svg>
                    <span x-show="$store.global.cartCount" class="badge-counter" x-text="$store.global.cartCount"></span>
                </a>

                <a href="{{ route('checkout.show') }}"
                   class="px-3 py-2 rounded-lg transition-colors flex-none whitespace-nowrap"
                   style="background: var(--accent); color: #071017; display:inline-flex; align-items:center; justify-content:center;"
                   aria-label="Proceed to Checkout">
                    <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="#FFFFFF" aria-hidden="true" focusable="false">
                        <path d="m480-560-56-56 63-64H320v-80h167l-64-64 57-56 160 160-160 160ZM280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM40-800v-80h131l170 360h280l156-280h91L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68.5-39t-1.5-79l54-98-144-304H40Z"/>
                    </svg>
                </a>

                @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2">
                        <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=40&d=identicon' }}" class="nav-avatar" alt="avatar">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" style="color: var(--accent)">Sign in</a>
                @endauth
            </div>
        </div>
    </nav>
    @yield('content')
 
 </body>
 
 {{-- allow pages to push additional scripts --}}
 @stack('scripts')
 </html>
