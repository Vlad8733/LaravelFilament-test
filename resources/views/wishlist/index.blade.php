@extends('layouts.app')

@section('title', __('wishlist.title') . ' - My Shop')

@push('styles')
    @vite('resources/css/wishlist/wishlistindex.css')
@endpush

@push('scripts')
    @vite('resources/js/wishlist/wishlistindex.js')
@endpush

@section('content')
<div x-data="wishlistPage()" class="wishlist-page">
    <!-- Toast Container -->
    <div class="toast-container">
        <template x-for="notification in notifications" :key="notification.id">
            <div x-show="notification.show" 
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-x-full"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-400"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-full"
                 :class="[notification.type, { 'hiding': notification.hiding }]"
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
                    <svg x-show="notification.type === 'warning'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="toast-content">
                    <div class="toast-product-name" x-text="notification.productName"></div>
                    <div class="toast-message" x-text="notification.message"></div>
                </div>
                <button @click="removeNotification(notification.id)" class="toast-close" type="button">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="toast-progress" :style="notification.hiding ? 'animation: none;' : ''"></div>
            </div>
        </template>
    </div>

    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="{{ route('products.index') }}">{{ __('wishlist.home') }}</a>
            <span>/</span>
            <span>{{ __('wishlist.wishlist') }}</span>
        </nav>

        <!-- Page Header -->
        <header class="page-header">
            <h1>{{ __('wishlist.your_wishlist') }}</h1>
            <span class="count">{{ $wishlistItems->count() }} {{ __('wishlist.items') }}</span>
        </header>

        @if($wishlistItems->count() > 0)
            <div class="wishlist-grid">
                @foreach($wishlistItems as $item)
                    <article class="wishlist-card" data-product-id="{{ $item->product->id }}">
                        <div class="card-thumb">
                            @if($item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" alt="{{ $item->product->name }}">
                            @else
                                <div class="placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            @endif
                            <button type="button"
                                    @click.prevent="removeFromWishlist({{ $item->product->id }}, '{{ addslashes($item->product->name) }}')"
                                    class="remove-btn">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="card-body">
                            <h3><a href="{{ route('products.show', $item->product) }}">{{ $item->product->name }}</a></h3>
                            <p class="card-category">{{ $item->product->category?->name ?? ($item->product->category()->first()?->name ?? __('wishlist.uncategorized')) }}</p>

                            @if($item->variant)
                                @php
                                    $v = $item->variant;
                                    $attrs = is_array($v->attributes) ? collect($v->attributes)->map(fn($val,$k) => "$k: $val")->join(', ') : null;
                                    $variantLabel = $attrs ? $attrs : ($v->sku ?? '');
                                @endphp
                                <p class="text-sm text-gray-500">{{ $variantLabel }}</p>
                            @endif

                            <div class="price-row">
                                <div class="price">
                                    @php $priceSource = $item->variant ?? $item->product; @endphp
                                    @if($priceSource->sale_price)
                                        <span class="price-current price-sale">${{ number_format($priceSource->sale_price, 2) }}</span>
                                        <span class="price-old">${{ number_format($priceSource->price, 2) }}</span>
                                    @else
                                        <span class="price-current">${{ number_format($priceSource->price ?? 0, 2) }}</span>
                                    @endif
                                </div>
                                <span class="badge-stock {{ $item->product->stock_quantity > 0 ? 'in' : 'out' }}">
                                    {{ $item->product->stock_quantity > 0 ? __('wishlist.in_stock') : __('wishlist.out_of_stock') }}
                                </span>
                            </div>

                            <div class="card-actions">
                                <button type="button" 
                                        @click.prevent="addToCart({{ $item->product->id }}, '{{ addslashes($item->product->name) }}', '{{ addslashes($variantLabel ?? '') }}', {{ $item->variant_id ?? 'null' }})"
                                        :disabled="loading"
                                        class="btn-cart"
                                        {{ ($item->variant ? ($item->variant->stock_quantity <= 0) : ($item->product->stock_quantity <= 0)) ? 'disabled' : '' }}>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                                    </svg>
                                    {{ $item->product->stock_quantity > 0 ? __('wishlist.add_to_cart') : __('wishlist.out_of_stock') }}
                                </button>
                                <a href="{{ route('products.show', $item->product) }}" class="btn-view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                    </svg>
                </div>
                <h2>{{ __('wishlist.empty') }}</h2>
                <p>{{ __('wishlist.empty_description') }}</p>
                <a href="{{ route('products.index') }}" class="btn-browse">
                    {{ __('wishlist.browse_products') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// Pass translations to JS
window.wishlistTranslations = {
    added_to_cart: @json(__('wishlist.added_to_cart')),
    removed: @json(__('wishlist.removed')),
    error_removing: @json(__('wishlist.error_removing')),
    error_adding_cart: @json(__('wishlist.error_adding_cart')),
    empty_title: @json(__('wishlist.empty_title')),
    empty_text: @json(__('wishlist.empty_subtitle')),
    browse_products: @json(__('wishlist.browse_products')),
    failed_remove: @json(__('wishlist.error_removing')),
    failed_add: @json(__('wishlist.error_adding_cart')),
    network_error: @json(__('wishlist.network_error') ?? 'Network error')
};
</script>
@endsection