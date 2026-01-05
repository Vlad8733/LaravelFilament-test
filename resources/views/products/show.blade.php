@extends('layouts.app')

@section('title', $product->name)

@push('styles')
    @vite(['resources/css/products/show.css','resources/css/products/productindex.css'])
@endpush

@push('scripts')
    @vite('resources/js/products/show.js')
@endpush

@section('content')
<div x-data="productPage()" x-init="init({{ $product->stock_quantity }}, {{ $product->id }})" class="product-page">
    <!-- Toast Notifications Container (same as products index) -->
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
    <div class="container">
        <!-- Breadcrumbs -->
        <nav class="breadcrumbs">
            <a href="{{ route('products.index') }}">{{ __('products.home') }}</a>
            <span>/</span>
            @if($product->category)
                <a href="{{ route('products.index', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a>
                <span>/</span>
            @endif
            <span>{{ $product->name }}</span>
        </nav>

        <!-- Product Section -->
        <div class="product-grid">
            <!-- Product Gallery -->
            <div class="product-gallery">
                @php $primary = ($product->images && $product->images->count() > 0) ? $product->images->first() : null; @endphp
                <div class="gallery-main">
                    @if($primary)
                        <img id="main-product-image"
                             src="{{ asset('storage/' . $primary->image_path) }}"
                             alt="{{ $primary->alt_text ?? $product->name }}">
                    @else
                        <div class="gallery-placeholder">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                @if($product->images && $product->images->count() > 1)
                    <div class="gallery-thumbs">
                        @foreach($product->images as $index => $image)
                            <button type="button"
                                    data-thumb-src="{{ asset('storage/' . $image->image_path) }}"
                                    data-thumb-index="{{ $index }}"
                                    class="thumb-btn {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     alt="{{ $image->alt_text ?? $product->name }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <div>
                    <h1 class="product-title">{{ $product->name }}</h1>
                    
                    <!-- Rating -->
                    <div class="product-rating">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="{{ $i <= round($product->average_rating ?? 0) ? 'filled' : 'empty' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="rating-text">
                            {{ number_format($product->average_rating ?? 0, 1) }} 
                            ({{ trans_choice('products.reviews_count', $product->reviews_count, ['count' => $product->reviews_count]) }})
                        </span>
                    </div>
                </div>

                <!-- Price -->
                <div class="product-price">
                    @if($product->sale_price)
                        <span id="product-price" data-base-price="{{ $product->sale_price }}" class="price-current">${{ number_format($product->sale_price, 2) }}</span>
                        <span id="product-old-price" data-base-old="{{ $product->price }}" class="price-old">${{ number_format($product->price, 2) }}</span>
                        <span id="product-discount" data-off-text="{{ __('products.off') }}" class="price-badge">
                            {{ $product->getDiscountPercentage() }}% {{ __('products.off') }}
                        </span>
                    @else
                        <span id="product-price" data-base-price="{{ $product->price }}" class="price-current">${{ number_format($product->price, 2) }}</span>
                        <span id="product-old-price" class="price-old" style="display:none;"></span>
                        <span id="product-discount" data-off-text="{{ __('products.off') }}" class="price-badge" style="display:none;"></span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div>
                    @if($product->stock_quantity > 0)
                        <span class="stock-badge in-stock">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $product->stock_quantity }} {{ __('products.in_stock') }}
                        </span>
                    @else
                        <span class="stock-badge out-of-stock">
                            {{ __('products.out_of_stock') }}
                        </span>
                    @endif
                </div>

                <!-- Description -->
                <div class="product-description">
                    <h3>{{ __('products.description') }}</h3>
                    <p>{{ $product->description }}</p>
                </div>

                <!-- Long Description -->
                @if($product->long_description)
                    <div class="product-description">
                        <h3>{{ __('products.details') }}</h3>
                        <div class="prose">
                            {!! nl2br(e($product->long_description)) !!}
                        </div>
                    </div>
                @endif

                <!-- Variants -->
                @if($product->variants && $product->variants->count() > 0)
                    <div class="product-variants">
                        <div class="variants-label">{{ __('products.variant') }}:</div>
                        <div id="product-variant-buttons" class="variants-grid">
                            @foreach($product->variants as $v)
                                @php
                                    $attrs = is_array($v->attributes) ? collect($v->attributes)->map(fn($val,$k) => "$k: $val")->join(', ') : null;
                                @endphp
                                <button type="button"
                                        class="variant-btn"
                                        data-variant-id="{{ $v->id }}"
                                        data-price="{{ $v->price }}"
                                        data-sale="{{ $v->sale_price }}"
                                        data-stock="{{ $v->stock_quantity }}"
                                        data-sku="{{ $v->sku }}"
                                        data-attrs="{{ e($attrs) }}">
                                    <div class="variant-icon">✦</div>
                                    <div class="variant-details">
                                        <div class="variant-name">{{ $attrs ?: $v->sku }}</div>
                                    </div>
                                    <div class="variant-meta">
                                        <div class="variant-price">${{ number_format($v->sale_price ?? $v->price ?? 0, 2) }}</div>
                                        <div class="variant-stock {{ $v->stock_quantity <= 0 ? 'out' : '' }}">
                                            @if($v->stock_quantity > 0) 
                                                {{ $v->stock_quantity }} {{ __('products.in_stock') }} 
                                            @else 
                                                {{ __('products.out_of_stock') }} 
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        <div id="product-variant-info" class="variant-info"></div>
                    </div>
                @endif

                <!-- Quantity -->
                <div class="quantity-row">
                    <span class="quantity-label">{{ __('products.quantity') }}:</span>
                    <div class="quantity-control">
                        <button type="button" data-qty-action="decrement" aria-label="Decrease quantity">−</button>
                        <input id="product-quantity" name="quantity" type="number" x-model.number="quantity"
                            min="1" :max="maxQuantity" value="1" aria-label="Product quantity" />
                        <button type="button" data-qty-action="increment" aria-label="Increase quantity">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button"
                            data-add-to-wishlist
                            data-product-id="{{ $product->id }}"
                            @click="addToWishlist({{ $product->id }})"
                            :disabled="loading"
                            class="btn-wishlist">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        {{ __('products.added_to_wishlist') ?? 'Add to Wishlist' }}
                    </button>

                    <button type="button"
                            data-add-to-cart
                            data-product-id="{{ $product->id }}"
                            data-max="{{ $product->stock_quantity }}"
                            @click="addToCart({{ $product->id }})"
                            :disabled="!canAddToCart || loading"
                            class="btn-cart">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        {{ __('products.add_to_cart') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">{{ __('products.customer_reviews') }}</h3>
                @if($product->reviews_count > 0)
                    <div class="product-rating">
                        <div class="stars">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="{{ $i <= round($product->average_rating ?? 0) ? 'filled' : 'empty' }}" 
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="rating-text">{{ $product->average_rating ?? 0 }} ({{ trans_choice('products.reviews_count', $product->reviews_count, ['count' => $product->reviews_count]) }})</span>
                    </div>
                @endif
            </div>
            
            @if($product->approvedReviews->count() > 0)
                <div class="reviews-list">
                    @foreach($product->approvedReviews as $review)
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-author">
                                    @php $user = $review->user; @endphp
                                    <img src="{{ $user?->avatar_url ?? asset('storage/logo/no_avatar.png') }}"
                                         alt="{{ $user?->name ?? 'User' }}"
                                         class="review-avatar">
                                    <div class="review-author-info">
                                        <h4>{{ $review->user->name ?? 'Anonymous' }}</h4>
                                        <div class="review-rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="{{ $i <= round($review->overall_rating) ? 'filled' : 'empty' }}" 
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span>{{ $review->overall_rating }}/5</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="review-date">{{ $review->created_at->translatedFormat('M j, Y') }}</span>
                            </div>
                            
                            <div class="review-breakdown">
                                <div class="breakdown-item">
                                    <span>{{ __('products.delivery') }}:</span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="{{ $i <= $review->delivery_rating ? 'filled' : 'empty' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <div class="breakdown-item">
                                    <span>{{ __('products.packaging') }}:</span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="{{ $i <= $review->packaging_rating ? 'filled' : 'empty' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <div class="breakdown-item">
                                    <span>{{ __('products.product') }}:</span>
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="{{ $i <= $review->product_rating ? 'filled' : 'empty' }}" 
                                             fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            
                            @if($review->comment)
                                <p class="review-comment">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <h4>{{ __('products.no_reviews_yet') }}</h4>
                    <p>{{ __('products.be_first_review') }}</p>
                </div>
            @endif
        </div>

        <!-- Related Products -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="section-card">
                <div class="section-header">
                    <h3 class="section-title">{{ __('products.related_products') }}</h3>
                </div>
                <div class="related-grid">
                    @foreach($relatedProducts as $relatedProduct)
                        <a href="{{ route('products.show', $relatedProduct) }}" class="related-card">
                            <div class="related-thumb">
                                @if($relatedProduct->getPrimaryImage())
                                    <img src="{{ asset('storage/' . $relatedProduct->getPrimaryImage()->image_path) }}" 
                                         alt="{{ $relatedProduct->name }}">
                                @endif
                            </div>
                            <div class="related-body">
                                <h4 class="related-name">{{ $relatedProduct->name }}</h4>
                                <div class="related-footer">
                                    <span class="related-price">${{ number_format($relatedProduct->getCurrentPrice(), 2) }}</span>
                                    <span class="related-link">{{ __('products.view_details') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Toast Notifications (Alpine) -->
    <div class="toast-container">
        <template x-for="(notification, index) in notifications.slice().reverse()" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="toast-enter"
                 x-transition:leave="toast-leave"
                 :class="{
                     'success': notification.type === 'success',
                     'error': notification.type === 'error'
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
</div>
@endsection