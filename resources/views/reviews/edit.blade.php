@extends('layouts.app')

@section('title', __('reviews.edit_review'))

@push('styles')
    @vite('resources/css/reviews/reviews.css')
@endpush

@section('content')
<div class="reviews-page">
    <div class="reviews-container">
        <a href="{{ route('reviews.show', $review) }}" class="reviews-back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('reviews.back_to_review') }}
        </a>

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
                <div class="reviews-card-header-info">
                    @if($review->product && $review->product->images->first())
                        <img src="{{ $review->product->images->first()->image_url }}" 
                             alt="{{ $review->product->name }}" 
                             class="reviews-product-image">
                    @else
                        <div class="reviews-product-placeholder">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="reviews-product-info">
                        <h1>{{ __('reviews.edit_review') }}</h1>
                        <p>{{ $review->product->name ?? 'Product' }}</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('reviews.update', $review) }}" method="POST" class="reviews-form">
                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="reviews-errors">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Ratings -->
                <div class="reviews-form-group">
                    <label class="reviews-form-label">{{ __('reviews.rate_experience') }}</label>
                    <div class="reviews-ratings-grid">
                        <!-- Delivery Rating -->
                        <div class="reviews-rating-item">
                            <span class="reviews-rating-item-label">{{ __('reviews.delivery') }}</span>
                            <div class="star-rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="delivery_rating" value="{{ $i }}" id="delivery_{{ $i }}" 
                                           {{ old('delivery_rating', $review->delivery_rating) == $i ? 'checked' : '' }} required>
                                    <label for="delivery_{{ $i }}" title="{{ $i }} stars">★</label>
                                @endfor
                            </div>
                        </div>

                        <!-- Packaging Rating -->
                        <div class="reviews-rating-item">
                            <span class="reviews-rating-item-label">{{ __('reviews.packaging') }}</span>
                            <div class="star-rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="packaging_rating" value="{{ $i }}" id="packaging_{{ $i }}"
                                           {{ old('packaging_rating', $review->packaging_rating) == $i ? 'checked' : '' }} required>
                                    <label for="packaging_{{ $i }}" title="{{ $i }} stars">★</label>
                                @endfor
                            </div>
                        </div>

                        <!-- Product Rating -->
                        <div class="reviews-rating-item">
                            <span class="reviews-rating-item-label">{{ __('reviews.product') }}</span>
                            <div class="star-rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="product_rating" value="{{ $i }}" id="product_{{ $i }}"
                                           {{ old('product_rating', $review->product_rating) == $i ? 'checked' : '' }} required>
                                    <label for="product_{{ $i }}" title="{{ $i }} stars">★</label>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comment -->
                <div class="reviews-form-group">
                    <label for="comment" class="reviews-form-label">{{ __('reviews.your_review_optional') }}</label>
                    <textarea name="comment" id="comment" rows="5"
                              class="reviews-textarea"
                              placeholder="{{ __('reviews.review_placeholder') }}">{{ old('comment', $review->comment) }}</textarea>
                    <p class="reviews-form-hint">{{ __('reviews.max_characters', ['count' => 2000]) }}</p>
                </div>

                <!-- Submit -->
                <div class="reviews-form-actions">
                    <a href="{{ route('reviews.show', $review) }}" class="reviews-btn reviews-btn-secondary">
                        {{ __('reviews.cancel') }}
                    </a>
                    <button type="submit" class="reviews-btn reviews-btn-primary">
                        {{ __('reviews.update_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection