@extends('layouts.app')

@section('title','My Wishlist - My Shop')

@push('styles')
    @vite('resources/css/wishlist/wishlistindex.css')
@endpush

@push('scripts')
    @vite('resources/js/wishlist/wishlistindex.js')
@endpush

@section('content')
<div x-data="wishlistPage()" class="wishlist-page">
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
                
                <!-- Icon -->
                <div class="toast-icon">
                    <!-- Success Icon -->
                    <svg x-show="notification.type === 'success'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <!-- Error Icon -->
                    <svg x-show="notification.type === 'error'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <!-- Info Icon -->
                    <svg x-show="notification.type === 'info'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <!-- Content -->
                <div class="toast-content">
                    <div class="toast-product-name" x-text="notification.productName"></div>
                    <div class="toast-message" x-text="notification.message"></div>
                </div>

                <!-- Close Button -->
                <button @click="removeNotification(notification.id)" class="toast-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Progress Bar -->
                <div class="toast-progress"></div>
            </div>
        </template>
    </div>

    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="mb-8">
            <ol class="flex items-center space-x-2 text-sm">
                <li><a href="{{ route('products.index') }}" class="text-blue-600 hover:text-blue-800">Home</a></li>
                <li class="text-gray-500">/</li>
                <li class="text-gray-900">Wishlist</li>
            </ol>
        </nav>

        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Your Wishlist</h1>
            <span class="text-sm text-gray-400">{{ $wishlistItems->count() }} items</span>
        </div>

        @if($wishlistItems->count() > 0)
            <div class="wishlist-grid">
                @foreach($wishlistItems as $item)
                    <div class="wishlist-card">
                        <div class="thumb">
                            @if($item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_path) }}" 
                                     alt="{{ $item->product->name }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif

                            <button type="button"
                                    data-wishlist-remove="{{ $item->product->id }}"
                                    @click.prevent="removeFromWishlist({{ $item->product->id }}, '{{ addslashes($item->product->name) }}')"
                                    class="remove-btn" aria-label="Remove from wishlist">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="p-4">
                            <h3>
                                <a href="{{ route('products.show', $item->product) }}" class="hover:text-blue-400">
                                    {{ $item->product->name }}
                                </a>
                            </h3>

                            <p class="text-sm">{{ $item->product->category->name ?? 'Uncategorized' }}</p>

                            <div class="flex items-center justify-between my-3">
                                @if($item->product->sale_price)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold text-green-400">${{ number_format($item->product->sale_price, 2) }}</span>
                                        <span class="text-sm text-gray-400 line-through">${{ number_format($item->product->price, 2) }}</span>
                                    </div>
                                @else
                                    <span class="text-lg font-bold text-gray-100">${{ number_format($item->product->price, 2) }}</span>
                                @endif

                                @if($item->product->stock_quantity > 0)
                                    <span class="badge-in">In Stock</span>
                                @else
                                    <span class="badge-out">Out of Stock</span>
                                @endif
                            </div>

                            <div class="actions">
                                <button type="button" 
                                        @click.prevent="addToCart({{ $item->product->id }}, '{{ addslashes($item->product->name) }}')"
                                        :disabled="loading || {{ $item->product->stock_quantity <= 0 ? 'true' : 'false' }}"
                                        class="btn-add text-sm">
                                    <span x-show="loading" x-cloak>Adding...</span>
                                    <span x-show="!loading" x-cloak>
                                        @if($item->product->stock_quantity > 0)
                                            Add to Cart
                                        @else
                                            Out of Stock
                                        @endif
                                    </span>
                                </button>

                                <a href="{{ route('products.show', $item->product) }}" class="btn-view">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="wishlist-empty">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                <h2 class="text-xl font-medium text-gray-300 mb-4">Your wishlist is empty</h2>
                <p class="text-gray-400 mb-6">Start adding products you love to keep track of them</p>
                <a href="{{ route('products.index') }}" class="btn-add">
                    Browse Products
                </a>
            </div>
        @endif
    </div>
</div>
@endsection