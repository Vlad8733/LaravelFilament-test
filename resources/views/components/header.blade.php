@props([
    'searchAction' => route('products.index'),
    'searchPlaceholder' => __('Search products...'),
])

<header class="header">
    <div class="header__inner">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="header__logo">
            <svg viewBox="0 0 24 24" fill="currentColor" width="32" height="32">
                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
            </svg>
            <span>e-<span class="text-primary">Shop</span></span>
        </a>

        {{-- Search --}}
        <form action="{{ $searchAction }}" method="GET" class="header__search">
            <div class="form-input-wrapper">
                <span class="form-input-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input 
                    type="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="header__search-input form-input"
                >
            </div>
        </form>

        {{-- Navigation --}}
        <nav class="header__nav">
            <a href="{{ route('home') }}" class="header__nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                {{ __('Home') }}
            </a>
            <a href="{{ route('products.index') }}" class="header__nav-link {{ request()->routeIs('products.*') ? 'is-active' : '' }}">
                {{ __('Products') }}
            </a>
            <a href="{{ route('pages.about') }}" class="header__nav-link {{ request()->routeIs('pages.about') ? 'is-active' : '' }}">
                {{ __('About') }}
            </a>
            <a href="{{ route('pages.contact') }}" class="header__nav-link {{ request()->routeIs('pages.contact') ? 'is-active' : '' }}">
                {{ __('Contact') }}
            </a>
        </nav>

        {{-- Actions --}}
        <div class="header__actions">
            {{-- Wishlist --}}
            @auth
                <a href="{{ route('wishlist.index') }}" class="header__action-btn" title="{{ __('Wishlist') }}">
                    <x-icons.heart />
                    @if(($wishlistCount = auth()->user()->wishlists()->count()) > 0)
                        <span class="header__action-badge">{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</span>
                    @endif
                </a>
            @endauth

            {{-- Cart --}}
            <a href="{{ route('cart.index') }}" class="header__action-btn" title="{{ __('Cart') }}">
                <x-icons.cart />
                @php
                    $cartCount = 0;
                    if (auth()->check()) {
                        $cartCount = \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
                    } else {
                        $cartCount = collect(session('cart', []))->sum('quantity');
                    }
                @endphp
                @if($cartCount > 0)
                    <span class="header__action-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>

            {{-- User menu --}}
            @auth
                <div class="dropdown" x-data="{ open: false }">
                    <button @click="open = !open" class="header__user">
                        <div class="avatar avatar--sm">
                            @if(auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="">
                            @else
                                {{ substr(auth()->user()->name, 0, 1) }}
                            @endif
                        </div>
                        <span class="header__user-name">{{ auth()->user()->name }}</span>
                    </button>
                    <div class="dropdown__menu" x-show="open" @click.away="open = false" x-transition>
                        <a href="{{ route('profile.edit') }}" class="dropdown__item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            {{ __('Profile') }}
                        </a>
                        <a href="{{ route('orders.index') }}" class="dropdown__item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            {{ __('Orders') }}
                        </a>
                        <a href="{{ route('settings.index') }}" class="dropdown__item">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            {{ __('Settings') }}
                        </a>
                        <div class="dropdown__divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown__item dropdown__item--danger w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                                </svg>
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn--primary btn--sm">
                    {{ __('Sign In') }}
                </a>
            @endauth
        </div>

        {{-- Mobile menu button --}}
        <button class="header__menu-btn" x-data @click="$dispatch('toggle-mobile-menu')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
    </div>
</header>
