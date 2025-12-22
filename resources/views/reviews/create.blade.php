@extends('layouts.app')

@section('title', __('reviews.write_review'))

@push('styles')
    @vite('resources/css/reviews/reviews.css')
@endpush

@section('content')
<div class="reviews-page">
    <div class="reviews-container">
        <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="reviews-back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('reviews.back_to_order') }}
        </a>

        @if(session('success'))
            <div class="reviews-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="reviews-alert error">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="reviews-card">
            <div class="reviews-card-header">
                <div class="reviews-product-info">
                    <h1>{{ __('reviews.write_review') }}</h1>
                    <p>{{ __('order.order_prefix', ['number' => $order->order_number]) }}</p>
                </div>
            </div>

            <form action="{{ route('reviews.store', $order) }}" method="POST" class="reviews-form" x-data="reviewForm()">
                @csrf

                @if($errors->any())
                    <div class="reviews-errors">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Product Selection -->
                @if($itemsToReview->count() > 1)
                    <div class="reviews-form-group">
                        <label class="reviews-form-label">{{ __('reviews.select_product') }}</label>
                        <div class="reviews-product-selector">
                            @foreach($itemsToReview as $item)
                                <label class="reviews-product-option">
                                    <input type="radio" name="product_id" value="{{ $item->product_id }}" 
                                           {{ $loop->first ? 'checked' : '' }}>
                                    <div class="reviews-product-option-content">
                                        @if($item->product && $item->product->images->first())
                                            <img src="{{ $item->product->images->first()->image_url }}" 
                                                 alt="{{ $item->product_name }}" 
                                                 class="reviews-item-image">
                                        @else
                                            <div class="reviews-item-placeholder">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="reviews-item-info">
                                            <div class="reviews-item-product">{{ $item->product_name }}</div>
                                            <div class="reviews-item-order">{{ __('reviews.qty') }}: {{ $item->quantity }} × ${{ number_format($item->product_price, 2) }}</div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    @php $item = $itemsToReview->first(); @endphp
                    <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                    <div class="reviews-product-card">
                        @if($item->product && $item->product->images->first())
                            <img src="{{ $item->product->images->first()->image_url }}" 
                                 alt="{{ $item->product_name }}" 
                                 class="reviews-product-image">
                        @else
                            <div class="reviews-product-placeholder">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <div class="reviews-product-name">{{ $item->product_name }}</div>
                            <div class="reviews-product-price">${{ number_format($item->total, 2) }}</div>
                        </div>
                    </div>
                @endif

                <!-- Ratings -->
                <div class="reviews-form-group">
                    <label class="reviews-form-label">{{ __('reviews.rate_experience') }}</label>
                    <div class="reviews-ratings-grid">
                        <!-- Delivery Rating -->
                        <div class="reviews-rating-category">
                            <span class="reviews-rating-label">{{ __('reviews.delivery') }}</span>
                            <div class="star-rating-input" x-data="{ rating: 0 }">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="delivery_rating" value="{{ $i }}" id="delivery_{{ $i }}" 
                                           x-on:change="rating = {{ $i }}" required>
                                    <label for="delivery_{{ $i }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <!-- Packaging Rating -->
                        <div class="reviews-rating-category">
                            <span class="reviews-rating-label">{{ __('reviews.packaging') }}</span>
                            <div class="star-rating-input" x-data="{ rating: 0 }">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="packaging_rating" value="{{ $i }}" id="packaging_{{ $i }}"
                                           x-on:change="rating = {{ $i }}" required>
                                    <label for="packaging_{{ $i }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <!-- Product Rating -->
                        <div class="reviews-rating-category">
                            <span class="reviews-rating-label">{{ __('reviews.product_quality') }}</span>
                            <div class="star-rating-input" x-data="{ rating: 0 }">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="product_rating" value="{{ $i }}" id="product_{{ $i }}"
                                           x-on:change="rating = {{ $i }}" required>
                                    <label for="product_{{ $i }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment -->
                <div class="reviews-form-group">
                    <label for="comment" class="reviews-form-label">{{ __('reviews.your_review_optional') }}</label>
                    <textarea name="comment" id="comment" rows="4"
                              class="reviews-textarea"
                              placeholder="{{ __('reviews.review_placeholder') }}"></textarea>
                    <p class="reviews-form-hint">{{ __('reviews.max_characters', ['count' => 2000]) }}</p>
                </div>

                <!-- Submit -->
                <div class="reviews-form-actions">
                    <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="reviews-btn reviews-btn-secondary">
                        {{ __('reviews.cancel') }}
                    </a>
                    <button type="submit" class="reviews-btn reviews-btn-primary">
                        {{ __('reviews.submit_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function reviewForm() {
    return {
        // Add any additional form logic here
    }
}
</script>
@endsection