@extends('layouts.app')

@section('title', $product->name)

@push('styles')
    @vite(['resources/css/products/show.css','resources/css/products/productindex.css'])
@endpush

@push('head-scripts')
    <script>
    // Pass current product data for Recently Viewed (must be before Alpine starts)
    window.currentProductId = {{ $product->id }};
    window.currentProductData = {
        id: {{ $product->id }},
        name: {!! json_encode($product->name) !!},
        price: {!! json_encode(number_format($product->getCurrentPrice(), 2)) !!},
        image: {!! json_encode($product->getPrimaryImage() ? asset('storage/' . $product->getPrimaryImage()->image_path) : null) !!},
        url: {!! json_encode(route('products.show', $product)) !!}
    };
    // Pass translations to JS for product page
    window.productTranslations = {
        added_to_cart: @json(__('products.added_to_cart')),
        added_to_wishlist: @json(__('products.added_to_wishlist')),
        removed_from_wishlist: @json(__('products.removed_from_wishlist')),
        network_error: @json(__('products.network_error')),
        out_of_stock: @json(__('products.out_of_stock')),
        in_stock: @json(__('products.in_stock')),
        in_stock_count: @json(__('products.in_stock_count')),
        select_variant_first: @json(__('products.select_variant_first')),
        variant_out_of_stock: @json(__('products.variant_out_of_stock')),
        failed_to_add_to_cart: @json(__('products.failed_to_add_to_cart')),
        error_adding_to_cart: @json(__('products.error_adding_to_cart')),
        failed_to_add_to_wishlist: @json(__('products.failed_to_add_to_wishlist')),
        requested_qty_not_available: @json(__('products.requested_qty_not_available')),
        adding: @json(__('products.adding'))
    };
    </script>
@endpush

@section('content')
<!-- Recently Viewed Tracker (hidden, just saves current product to localStorage) -->
<div x-data="rvComponent()" x-init="init()" style="display:none;"></div>

<div x-data="productPage()" x-init="init({{ $product->stock_quantity }}, {{ $product->id }})" class="product-page">
    <!-- Toast Notifications Container -->
    <div class="toast-container">
        <template x-for="(notification, index) in notifications.slice().reverse()" :key="notification.id">
            <div x-show="notification.show"
                 x-transition:enter="toast-enter"
                 x-transition:enter-start="toast-enter-start"
                 x-transition:enter-end="toast-enter-end"
                 x-transition:leave="toast-leave"
                 x-transition:leave-start="toast-leave-start"
                 x-transition:leave-end="toast-leave-end"
                 :class="{
                     'success': notification.type === 'success',
                     'error': notification.type === 'error',
                     'info': notification.type === 'info',
                     'warning': notification.type === 'warning'
                 }"
                 class="toast-notification">
                <div class="toast-icon">
                    <svg x-show="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="m424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                    </svg>
                    <svg x-show="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                    </svg>
                    <svg x-show="notification.type === 'info'" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                    </svg>
                    <svg x-show="notification.type === 'warning'" xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                    </svg>
                </div>
                <div class="toast-content">
                    <div class="toast-product-name" x-text="notification.productName"></div>
                    <div class="toast-message" x-text="notification.message"></div>
                </div>
                <button @click="removeNotification(notification.id)" class="toast-close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor">
                        <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                    </svg>
                </button>
                <div class="toast-progress"></div>
            </div>
        </template>
    </div>
    <div class="container">
        <!-- Breadcrumbs -->
        @php
            $category = $product->category ?? $product->category()->first();
        @endphp
        <x-breadcrumbs :items="array_filter([
            ['label' => __('products.all_products'), 'url' => route('products.index')],
            $category ? ['label' => $category->name, 'url' => route('products.index', ['category' => $category->id])] : null,
            ['label' => $product->name]
        ])" />

        <!-- Product Section -->
        <div class="product-grid">
            <!-- Product Gallery -->
            <div class="product-gallery">
                @php $primary = ($product->images && $product->images->count() > 0) ? $product->images->first() : null; @endphp
                <div class="gallery-main" 
                     x-data="{ zooming: false, zoomLevel: 2.5 }"
                     @mouseenter="if(window.innerWidth > 1024) { zooming = true; $nextTick(() => { if($refs.mainImage && $refs.result) { $refs.result.style.backgroundImage = 'url(' + $refs.mainImage.src + ')'; $refs.result.style.backgroundSize = ($refs.mainImage.offsetWidth * zoomLevel) + 'px ' + ($refs.mainImage.offsetHeight * zoomLevel) + 'px'; } }); }"
                     @mousemove="if(!zooming) return; let img = $refs.mainImage; let lens = $refs.lens; let result = $refs.result; let rect = img.getBoundingClientRect(); let x = $event.clientX - rect.left; let y = $event.clientY - rect.top; let lensW = lens.offsetWidth; let lensH = lens.offsetHeight; let lensX = Math.max(0, Math.min(x - lensW/2, img.offsetWidth - lensW)); let lensY = Math.max(0, Math.min(y - lensH/2, img.offsetHeight - lensH)); lens.style.left = lensX + 'px'; lens.style.top = lensY + 'px'; result.style.backgroundPosition = '-' + (lensX * zoomLevel) + 'px -' + (lensY * zoomLevel) + 'px';"
                     @mouseleave="zooming = false">
                    @if($primary)
                        <img id="main-product-image"
                             src="{{ asset('storage/' . $primary->image_path) }}"
                             alt="{{ $primary->alt_text ?? $product->name }}"
                             x-ref="mainImage">
                        <!-- Zoom Lens -->
                        <div class="zoom-lens" x-show="zooming" x-ref="lens" x-cloak></div>
                    @else
                        <div class="gallery-placeholder">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    <!-- Zoom Result (large preview) -->
                    <div class="zoom-result" x-show="zooming" x-ref="result" x-cloak></div>
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
</div>

@auth
    @if($product->company && $product->company->user_id && Auth::id() !== $product->company->user_id)
        <!-- Fixed Chat Button -->
        <a href="{{ route('product-chat.show', $product) }}" 
           class="chat-fab"
           title="{{ __('product_chat.chat_with_seller') }}">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="chat-fab-tooltip">{{ __('product_chat.chat_with_seller') }}</span>
        </a>
    @endif
@endauth
@endsection

@push('scripts')
    @vite(['resources/js/products/show.js'])
@endpush