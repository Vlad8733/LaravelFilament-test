<!DOCTYPE html>
<html lang="en" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ShopLy')</title>

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
                loaded: false,
                
                toggle() {
                    this.open = !this.open;
                    if (this.open && !this.loaded) {
                        this.fetchNotifications();
                    }
                },
                
                async fetchNotifications() {
                    if (this.loading) return;
                    this.loading = true;
                    try {
                        const response = await fetch('/notifications/unread', {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.notifications = data.notifications || [];
                            Alpine.store('global').notificationsCount = data.count || 0;
                            this.loaded = true;
                        }
                    } catch (error) {
                        console.error('Failed to fetch notifications:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                async markAsRead(id) {
                    try {
                        await fetch('/notifications/' + id + '/read', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                        this.notifications = this.notifications.filter(n => n.id !== id);
                        const store = Alpine.store('global');
                        if (store.notificationsCount > 0) {
                            store.notificationsCount--;
                        }
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
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
                        Alpine.store('global').notificationsCount = 0;
                    } catch (error) {
                        console.error('Failed to mark as read:', error);
                    }
                },
                
                getNotificationUrl(notification) {
                    const data = notification.data || {};
                    if (data.ticket_id) {
                        return '/support/' + data.ticket_id;
                    }
                    if (data.order_id) {
                        return '/track-order/' + data.order_id;
                    }
                    if (data.url) {
                        return data.url;
                    }
                    return '#';
                },
                
                getNotificationIcon(notification) {
                    const type = notification.type || '';
                    if (type.includes('Ticket')) {
                        return 'ticket';
                    }
                    if (type.includes('Order')) {
                        return 'order';
                    }
                    return 'default';
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
                },
                setCartCount(count) {
                    this.cartCount = Number(count);
                },
                setWishlistCount(count) {
                    this.wishlistCount = Number(count);
                },
                setNotificationsCount(count) {
                    this.notificationsCount = Number(count);
                },
                incrementWishlist() {
                    this.wishlistCount++;
                },
                decrementWishlist() {
                    if (this.wishlistCount > 0) this.wishlistCount--;
                },
                incrementCart() {
                    this.cartCount++;
                },
                decrementCart() {
                    if (this.cartCount > 0) this.cartCount--;
                }
            });

            // Auto-refresh notifications every 10 seconds
            setInterval(async () => {
                try {
                    const response = await fetch('/notifications/unread', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (response.ok) {
                        const data = await response.json();
                        const newCount = data.count || 0;
                        const currentCount = Alpine.store('global').notificationsCount;
                        
                        if (newCount > currentCount) {
                            // Плавная анимация для нового уведомления
                            Alpine.store('global').setNotificationsCount(newCount);
                        } else {
                            Alpine.store('global').setNotificationsCount(newCount);
                        }
                    }
                } catch (error) {
                    // Silently fail
                }
            }, 10000);

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
        
        window.updateWishlistCount = function(count) {
            if (typeof Alpine !== 'undefined' && Alpine.store('global')) {
                Alpine.store('global').setWishlistCount(count);
            }
        };
        
        window.incrementWishlistCount = function() {
            if (typeof Alpine !== 'undefined' && Alpine.store('global')) {
                Alpine.store('global').incrementWishlist();
            }
        };
        
        window.decrementWishlistCount = function() {
            if (typeof Alpine !== 'undefined' && Alpine.store('global')) {
                Alpine.store('global').decrementWishlist();
            }
        };
        
        window.updateCartCount = function(count) {
            if (typeof Alpine !== 'undefined' && Alpine.store('global')) {
                Alpine.store('global').setCartCount(count);
            }
        };
    </script>

    <style>
        :root {
            --nav-h: 64px;
            --bg: #071017;
            --nav-bg: linear-gradient(180deg,#1a1a1a,#141414);
            --accent: #f59e0b;
        }
        
        [x-cloak] { display: none !important; }

        html { overflow-y: scroll; }

        body {
            margin: 0;
            padding-top: var(--nav-h);
            background: var(--bg);
            color: #e5e7eb;
        }

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

        .logo-img { height: 40px; width: auto; display: block; }

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
            cursor: pointer;
            color: #9f9e9e;
            transition: color 0.2s ease;
        }
        
        .nav-item-wrap:hover { color: var(--accent); }

        .badge-counter {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #ef4444;
            color: #fff;
            min-width: 18px;
            height: 18px;
            font-size: 11px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        .nav-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            object-fit: cover;
        }
        
        .user-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #e5e7eb;
        }
        
        .user-name {
            font-size: 0.875rem;
            font-weight: 500;
        }

        @media (max-width: 768px) { .user-name { display: none; } }

        #site-nav .logo-img {
            width: auto !important;
            height: 40px !important;
            max-width: none !important;
        }

        .search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
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
        }

        .search-result-item:hover,
        .search-result-item.active { background: rgba(245,158,11,0.08); }

        .sr-thumb { 
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            background: #0f0f0f;
        }

        .sr-meta { display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .sr-name { font-weight: 600; font-size: 0.9375rem; color: #e5e7eb; }
        .sr-price { color: var(--accent); font-size: 0.875rem; font-weight: 600; }
        .sr-empty { padding: 16px; color: #9ca3af; text-align: center; }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 380px;
            max-height: 480px;
            background: linear-gradient(180deg, #1a1a1a, #141414);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            z-index: 1100;
        }
        
        .notification-dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        
        .notification-dropdown-title { font-weight: 600; font-size: 1rem; color: #e5e7eb; }
        .notification-dropdown-action { font-size: 0.8125rem; color: var(--accent); cursor: pointer; }
        .notification-dropdown-list { max-height: 400px; overflow-y: auto; }
        .notification-dropdown-loading { padding: 40px 20px; text-align: center; color: #9ca3af; }
        .notification-dropdown-empty { padding: 40px 20px; text-align: center; color: #6b7280; }
        
        .notification-item {
            display: flex;
            gap: 12px;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            cursor: pointer;
            transition: background 0.15s ease;
            text-decoration: none;
            color: inherit;
        }
        
        .notification-item:hover { background: rgba(255,255,255,0.03); }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .notification-icon.ticket { background: rgba(139, 92, 246, 0.15); color: #a78bfa; }
        .notification-icon.order { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
        .notification-icon.default { background: rgba(245, 158, 11, 0.15); color: var(--accent); }
        
        .notification-content { flex: 1; min-width: 0; }
        .notification-message { font-size: 0.9rem; color: #e5e7eb; line-height: 1.4; margin-bottom: 4px; }
        .notification-time { font-size: 0.75rem; color: #6b7280; }
        
        .checkout-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            background: var(--accent);
            color: #000;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            gap: 6px;
            transition: opacity 0.2s;
        }
        
        .checkout-btn:hover { opacity: 0.9; }

        /* Profile Dropdown */
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 280px;
            background: linear-gradient(180deg, #1a1a1a, #141414);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            overflow: hidden;
            z-index: 1100;
        }
        
        .profile-dropdown-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: rgba(255,255,255,0.02);
        }
        
        .profile-dropdown-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(245, 158, 11, 0.3);
        }
        
        .profile-dropdown-info {
            flex: 1;
            min-width: 0;
        }
        
        .profile-dropdown-name {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #e5e7eb;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .profile-dropdown-email {
            font-size: 0.8125rem;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .profile-dropdown-divider {
            height: 1px;
            background: rgba(255,255,255,0.06);
        }
        
        .profile-dropdown-section {
            padding: 8px;
        }
        
        .profile-dropdown-section-title {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            padding: 8px 12px 4px;
        }
        
        .profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 8px;
            color: #d1d5db;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.15s ease;
            cursor: pointer;
            width: 100%;
            background: none;
            border: none;
            text-align: left;
        }
        
        .profile-dropdown-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        
        .profile-dropdown-item svg {
            flex-shrink: 0;
            opacity: 0.7;
        }
        
        .profile-dropdown-item:hover svg {
            opacity: 1;
        }
        
        .profile-dropdown-item span {
            flex: 1;
        }
        
        .profile-dropdown-badge {
            background: var(--accent);
            color: #000;
            font-size: 0.6875rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        .profile-dropdown-item.admin-link {
            color: #a78bfa;
        }
        
        .profile-dropdown-item.admin-link:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #c4b5fd;
        }
        
        .profile-dropdown-item.logout-btn {
            color: #f87171;
        }
        
        .profile-dropdown-item.logout-btn:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #fca5a5;
        }
        
        .chevron-icon {
            opacity: 0.5;
        }
        
        .rotate-180 {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <nav id="site-nav">
        <div class="nav-wrap">
            <a href="{{ url('/products') }}">
                <img src="{{ asset('storage/logo/logoShopLy.png') }}" class="logo-img" alt="ShopLy">
            </a>

            <div class="relative" x-data="searchBox()" @click.away="showResults=false">
                <svg class="icon-left w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input class="search-input" placeholder="Search products…" x-model="query" @input="debounceSearch" @keydown.arrow-down.prevent="next()" @keydown.arrow-up.prevent="prev()" @keydown.enter.prevent="select()">

                <div x-show="showResults" x-cloak class="search-results" x-transition>
                    <template x-for="(item, idx) in results" :key="item.id">
                        <a :href="item.url" class="search-result-item" :class="{'active': idx === selectedIndex}">
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

            <div class="nav-actions">
                @auth
                    <!-- Notifications -->
                    <div class="relative" x-data="notificationDropdown()" @click.away="open = false">
                        <div class="nav-item-wrap" @click="toggle()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span x-show="$store.global.notificationsCount > 0" class="badge-counter" x-text="$store.global.notificationsCount" x-cloak></span>
                        </div>
                        
                        <div x-show="open" x-cloak x-transition class="notification-dropdown">
                            <div class="notification-dropdown-header">
                                <span class="notification-dropdown-title">Notifications</span>
                                <span x-show="notifications.length > 0" @click="markAllAsRead()" class="notification-dropdown-action">Mark all read</span>
                            </div>
                            
                            <div class="notification-dropdown-list">
                                <div x-show="loading" class="notification-dropdown-loading">Loading...</div>
                                
                                <template x-if="!loading && notifications.length === 0">
                                    <div class="notification-dropdown-empty">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; opacity: 0.4;">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        </svg>
                                        <div>No new notifications</div>
                                    </div>
                                </template>
                                
                                <template x-for="notification in notifications" :key="notification.id">
                                    <a :href="getNotificationUrl(notification)" @click="markAsRead(notification.id)" class="notification-item">
                                        <div x-show="getNotificationIcon(notification) === 'ticket'" class="notification-icon ticket">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                            </svg>
                                        </div>
                                        <div x-show="getNotificationIcon(notification) === 'order'" class="notification-icon order">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                            </svg>
                                        </div>
                                        <div x-show="getNotificationIcon(notification) === 'default'" class="notification-icon default">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            </svg>
                                        </div>
                                        
                                        <div class="notification-content">
                                            <div class="notification-message" x-text="notification.data?.message || 'New notification'"></div>
                                            <div class="notification-time" x-text="notification.created_at_human || ''"></div>
                                        </div>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Wishlist -->
                    <a href="{{ route('wishlist.index') }}" class="nav-item-wrap">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span x-show="$store.global.wishlistCount > 0" class="badge-counter" x-text="$store.global.wishlistCount" x-cloak></span>
                    </a>

                    <!-- Cart -->
                    <a href="{{ route('cart.index') }}" class="nav-item-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor">
                            <path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/>
                        </svg>
                        <span x-show="$store.global.cartCount > 0" class="badge-counter" x-text="$store.global.cartCount" x-cloak></span>
                    </a>

                    <!-- Checkout -->
                    <a href="{{ route('checkout.show') }}" class="checkout-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 -960 960 960" width="18" fill="currentColor">
                            <path d="m480-560-56-56 63-64H320v-80h167l-64-64 57-56 160 160-160 160ZM280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM40-800v-80h131l170 360h280l156-280h91L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68.5-39t-1.5-79l54-98-144-304H40Z"/>
                        </svg>
                        Checkout
                    </a>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                        <button @click="profileOpen = !profileOpen" class="user-link" style="background: none; border: none; cursor: pointer;">
                            <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=40&d=identicon' }}" class="nav-avatar" alt="avatar">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron-icon" :class="{ 'rotate-180': profileOpen }" style="transition: transform 0.2s;">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </button>
                        
                        <div x-show="profileOpen" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="profile-dropdown">
                            
                            <!-- User Info Header -->
                            <div class="profile-dropdown-header">
                                <img src="{{ auth()->user()->avatar ? asset('storage/'.auth()->user()->avatar) : 'https://www.gravatar.com/avatar/'.md5(strtolower(trim(auth()->user()->email))).'?s=60&d=identicon' }}" class="profile-dropdown-avatar" alt="avatar">
                                <div class="profile-dropdown-info">
                                    <div class="profile-dropdown-name">{{ auth()->user()->name }}</div>
                                    <div class="profile-dropdown-email">{{ auth()->user()->email }}</div>
                                </div>
                            </div>
                            
                            <div class="profile-dropdown-divider"></div>
                            
                            <!-- Navigation Links -->
                            <div class="profile-dropdown-section">
                                <a href="{{ route('profile.edit') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                    <span>My Profile</span>
                                </a>
                                
                                <a href="{{ route('orders.tracking.search') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                    <span>Track Orders</span>
                                </a>
                                
                                <a href="{{ route('tickets.index') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    <span>Support Tickets</span>
                                </a>
                                
                                <a href="{{ route('refunds.index') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                    </svg>
                                    <span>My Refunds</span>
                                </a>
                            </div>
                            
                            <div class="profile-dropdown-divider"></div>
                            
                            <!-- Shopping Section 
                            <div class="profile-dropdown-section">
                                <div class="profile-dropdown-section-title">Shopping</div>
                                
                                <a href="{{ route('wishlist.index') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span>Wishlist</span>
                                    <span x-show="$store.global.wishlistCount > 0" class="profile-dropdown-badge" x-text="$store.global.wishlistCount"></span>
                                </a>
                                
                                <a href="{{ route('cart.index') }}" class="profile-dropdown-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"></circle>
                                        <circle cx="20" cy="21" r="1"></circle>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                    </svg>
                                    <span>Cart</span>
                                    <span x-show="$store.global.cartCount > 0" class="profile-dropdown-badge" x-text="$store.global.cartCount"></span>
                                </a>
                            </div>-->
                            
                            <div class="profile-dropdown-divider"></div>
                            
                            <!-- Account Actions -->
                            <div class="profile-dropdown-section">
                                @if(auth()->user()->is_admin)
                                <a href="/admin" class="profile-dropdown-item admin-link">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"></path>
                                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"></path>
                                    </svg>
                                    <span>Admin Panel</span>
                                </a>
                                @endif
                                
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="profile-dropdown-item logout-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                            <polyline points="16 17 21 12 16 7"></polyline>
                                            <line x1="21" y1="12" x2="9" y2="12"></line>
                                        </svg>
                                        <span>Sign Out</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" style="color: var(--accent); font-weight: 500;">Sign in</a>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')
    
    @stack('scripts')
</body>
</html>