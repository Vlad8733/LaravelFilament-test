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
                                    <!-- placeholder svg -->
                                </div>
                            @endif

                            <button type="button"
                                    data-wishlist-remove="{{ $item->product->id }}"
                                    @click.prevent="removeFromWishlist({{ $item->product->id }})"
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
                                <button type="button" @click.prevent="addToCart({{ $item->product->id }})"
                                        :disabled="loading || {{ $item->product->stock_quantity > 0 ? 'false' : 'true' }}"
                                        class="btn-add text-sm">
                                    Add to Cart
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

        <!-- Unified notification (same behavior / animation as products index) -->
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
    </div>
</div>
@endsection