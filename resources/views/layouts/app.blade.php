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
        window.initialCartCount = {{ auth()->check() ? auth()->user()->cartItems()->sum('quantity') : 0 }};
        window.initialWishlistCount = {{ auth()->check() ? auth()->user()->wishlistItems()->count() : 0 }};
        window.initialNotificationsCount = {{ auth()->check() ? auth()->user()->unreadNotifications()->count() : 0 }};

        document.addEventListener('alpine:init', () => {
            Alpine.data('searchBox', () => ({
                query: '',
                results: [],
                showResults: false,
                selectedIndex: -1,
                debounceTimeout: null,
                debounceSearch() {
                    clearTimeout(this.debounceTimeout);
                    this.debounceTimeout = setTimeout(() => this.search(), 250);
                },
                search() {
                    if (!this.query || this.query.length < 2) {
                        this.results = [];
                        this.showResults = false;
                        this.selectedIndex = -1;
                        return;
                    }
                    fetch('/search?query=' + encodeURIComponent(this.query), { headers: { 'Accept': 'application/json' } })
                        .then(res => {
                            if (!res.ok) throw new Error('network');
                            return res.json();
                        })
                        .then(data => {
                            this.results = Array.isArray(data) ? data : [];
                            this.showResults = this.results.length > 0;
                            this.selectedIndex = this.results.length ? 0 : -1;
                        })
                        .catch(() => { this.results = []; this.showResults = false; this.selectedIndex = -1; });
                },
                next() {
                    if (!this.showResults) return;
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.results.length - 1);
                },
                prev() {
                    if (!this.showResults) return;
                    this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                },
                select() {
                    if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                        window.location = this.results[this.selectedIndex].url;
                    }
                }
            }));

            Alpine.data('notificationDropdown', () => ({
                open: false,
                notifications: [],
                loading: false,
                async fetchNotifications() {
                    if (this.notifications.length > 0) return;
                    this.loading = true;
                    try {
                        const response = await fetch('/notifications/unread');
                        const data = await response.json();
                        this.notifications = data.notifications;
                        this.$store.global.notificationsCount = data.count;
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                async markAllAsRead() {
                    try {
                        await fetch('/notifications/mark-all-read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        this.notifications = [];
                        this.$store.global.notificationsCount = 0;
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                }
            }));

            Alpine.store('global', {
                cartCount: Number(window.initialCartCount || 0),
                wishlistCount: Number(window.initialWishlistCount || 0),
                notificationsCount: Number(window.initialNotificationsCount || 0),
                markViewed(type) {
                    if (type === 'cart') this.cartCount = 0;
                    if (type === 'wishlist') this.wishlistCount = 0;
                    if (type === 'notifications') this.notificationsCount = 0;
                }
            });

            Alpine.data('checkout', () => ({
                customerName: '',
                customerEmail: '',
                shippingAddress: '',
                notes: '',
                showPaymentModal: false,
                paymentMethod: 'card',
                cardNumber: '',
                cardExpiry: '',
                cardCvv: '',
                cardName: '',
                processing: false,
                errors: {},

                init() {
                    console.log('Checkout Alpine component initialized');
                },

                openPaymentModal() {
                    if (!this.customerName || !this.customerEmail || !this.shippingAddress) {
                        alert('Please fill in all shipping information');
                        return;
                    }
                    this.showPaymentModal = true;
                },

                formatCardNumber() {
                    let value = this.cardNumber.replace(/\D/g, '');
                    this.cardNumber = value.replace(/(\d{4})/g, '$1 ').trim();
                },

                formatExpiry() {
                    let value = this.cardExpiry.replace(/\D/g, '');
                    if (value.length >= 2) {
                        this.cardExpiry = value.slice(0, 2) + '/' + value.slice(2, 4);
                    } else {
                        this.cardExpiry = value;
                    }
                },

                async submitOrder() {
                    if (this.processing) return;

                    if (this.paymentMethod === 'card') {
                        if (!this.cardNumber || !this.cardExpiry || !this.cardCvv || !this.cardName) {
                            alert('Please fill in all card details');
                            return;
                        }
                    }

                    this.processing = true;

                    try {
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                        formData.append('name', this.customerName);
                        formData.append('email', this.customerEmail);
                        formData.append('address', this.shippingAddress);
                        formData.append('notes', this.notes || '');
                        formData.append('payment_method', 'fake');

                        const response = await fetch('/checkout', {
                            method: 'POST',
                            body: formData
                        });

                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            const data = await response.json();
                            if (!data.success) {
                                alert(data.message || 'Payment failed');
                            }
                        }
                    } catch (error) {
                        console.error('Checkout error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.processing = false;
                    }
                }
            }));
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

        /* search dropdown */
        .search-results {
            position: absolute;
            top: calc(var(--nav-h) + 8px);
            left: 0;
            right: 0;
            margin-top: 6px;
            background: linear-gradient(180deg,#0f1113,#0b0b0b);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 10px;
            max-height: 56vh;
            overflow: auto;
            z-index: 1200;
            padding: 8px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .search-result-item {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            color: inherit;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .search-result-item:hover,
        .search-result-item.active { 
            background: rgba(245,158,11,0.08); 
        }

        .sr-thumb { 
            width: 56px;
            height: 56px;
            min-width: 56px;
            min-height: 56px;
            object-fit: cover;
            border-radius: 8px;
            background: #0f0f0f;
            border: 1px solid rgba(255,255,255,0.06);
            display: block;
        }

        /* Fallback for missing images */
        .sr-thumb-placeholder {
            width: 56px;
            height: 56px;
            min-width: 56px;
            min-height: 56px;
            border-radius: 8px;
            background: #0f0f0f;
            border: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sr-thumb-placeholder svg {
            width: 24px;
            height: 24px;
            color: rgba(255,255,255,0.2);
        }

        .sr-meta { 
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 0;
        }

        .sr-name { 
            font-weight: 600;
            font-size: 0.9375rem;
            line-height: 1.3;
            color: #e5e7eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sr-price { 
            color: var(--accent);
            font-size: 0.875rem;
            font-weight: 600;
        }

        .sr-empty { 
            padding: 16px;
            color: #9ca3af;
            text-align: center;
            font-size: 0.875rem;
        }

        /* Loading state */
        .sr-loading {
            padding: 16px;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
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
            <div class="relative" x-data="searchBox()" @click.away="showResults=false">
                <svg class="icon-left w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input class="search-input" placeholder="Search products…" x-model="query" @input="debounceSearch" @keydown.arrow-down.prevent="next()" @keydown.arrow-up.prevent="prev()" @keydown.enter.prevent="select()">

                <!-- results panel -->
                <div x-show="showResults" x-cloak class="search-results" x-transition>
                    <template x-for="(item, idx) in results" :key="item.id">
                        <a :href="item.url" class="search-result-item" :class="{'active': idx === selectedIndex}" @mouseenter="selectedIndex = idx" @click.prevent="window.location = item.url">
                            <img x-show="item.image" :src="item.image" alt="" class="sr-thumb">
                            <div class="sr-meta">
                                <div class="sr-name" x-text="item.name"></div>
                                <div class="sr-price" x-text="item.price ? ('$' + item.price) : ''"></div>
                            </div>
                        </a>
                    </template>
                    <div x-show="results.length === 0" class="sr-empty">No results</div>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="nav-actions">
                @auth
                    <!-- Notifications -->
                    <div class="relative" x-data="notificationDropdown()" @click.away="open = false">
                        <div class="nav-item-wrap" @click="open = !open; if(open) fetchNotifications()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span x-show="$store.global.notificationsCount > 0" class="badge-counter" x-text="$store.global.notificationsCount" x-cloak></span>
                        </div>
                    </div>

                    <!-- Support Tickets Link -->
                    <a href="{{ route('tickets.index') }}" class="nav-item-wrap" title="Support Tickets">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </a>

                    <!-- Track Order -->
                    <a href="{{ route('orders.tracking.search') }}" 
                       class="nav-item-wrap" 
                       title="Track Order">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                        </svg>
                    </a>
                @endauth

                <a href="{{ route('wishlist.index') }}" class="nav-item-wrap" @click="$store.global.markViewed('wishlist')">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span x-show="$store.global.wishlistCount" class="badge-counter" x-text="$store.global.wishlistCount"></span>
                </a>

                <a href="{{ route('cart.index') }}" class="nav-item-wrap" @click="$store.global.markViewed('cart')">
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
    @stack('scripts')
</body>
</html>