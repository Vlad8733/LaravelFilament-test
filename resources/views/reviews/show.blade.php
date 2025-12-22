@extends('layouts.app')

@section('title', __('reviews.title'))

@push('styles')
    @vite('resources/css/reviews/reviews.css')
@endpush

@section('content')
<div class="reviews-page">
    <div class="reviews-container">
        <a href="{{ route('reviews.index') }}" class="reviews-back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('reviews.back_to_reviews') }}
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
            <!-- Header -->
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
                        <h1>{{ $review->product->name ?? 'Product' }}</h1>
                        <p>{{ __('order.order_prefix', ['number' => $review->order->order_number]) }}</p>
                    </div>
                </div>
                <span class="reviews-status {{ $review->status }}">
                    <span class="reviews-status-dot"></span>
                    {{ $review->status_label }}
                </span>
            </div>

            <!-- Overall Rating -->
            <div class="reviews-detail-section">
                <div class="reviews-section-label">{{ __('reviews.overall_rating') }}</div>
                <div class="reviews-overall">
                    <span class="reviews-overall-value">{{ number_format($review->overall_rating, 1) }}</span>
                    <div class="reviews-overall-stars">
                        <div class="reviews-stars large">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="{{ $i <= round($review->overall_rating) ? 'reviews-star-filled' : 'reviews-star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="reviews-overall-date">{{ $review->created_at->translatedFormat('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Ratings Breakdown -->
            <div class="reviews-detail-section">
                <div class="reviews-section-label">{{ __('reviews.ratings_breakdown') }}</div>
                <div class="reviews-breakdown">
                    <div class="reviews-breakdown-item">
                        <span class="reviews-breakdown-label">{{ __('reviews.delivery') }}</span>
                        <div class="reviews-breakdown-stars">
                            <div class="reviews-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="{{ $i <= $review->delivery_rating ? 'reviews-star-filled' : 'reviews-star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <span class="reviews-breakdown-value">{{ $review->delivery_rating }}/5</span>
                    </div>
                    <div class="reviews-breakdown-item">
                        <span class="reviews-breakdown-label">{{ __('reviews.packaging') }}</span>
                        <div class="reviews-breakdown-stars">
                            <div class="reviews-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="{{ $i <= $review->packaging_rating ? 'reviews-star-filled' : 'reviews-star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <span class="reviews-breakdown-value">{{ $review->packaging_rating }}/5</span>
                    </div>
                    <div class="reviews-breakdown-item">
                        <span class="reviews-breakdown-label">{{ __('reviews.product_quality') }}</span>
                        <div class="reviews-breakdown-stars">
                            <div class="reviews-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="{{ $i <= $review->product_rating ? 'reviews-star-filled' : 'reviews-star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                        </div>
                        <span class="reviews-breakdown-value">{{ $review->product_rating }}/5</span>
                    </div>
                </div>
            </div>

            <!-- Comment -->
            <div class="reviews-detail-section">
                <div class="reviews-section-label">{{ __('reviews.your_review') }}</div>
                @if($review->comment)
                    <p class="reviews-comment">{{ $review->comment }}</p>
                @else
                    <p class="reviews-comment-placeholder">{{ __('reviews.no_comment') }}</p>
                @endif
            </div>

            <!-- Moderation Notes (if rejected) -->
            @if($review->isRejected() && $review->moderation_notes)
                <div class="reviews-detail-section">
                    <div class="reviews-moderation">
                        <div class="reviews-moderation-label">{{ __('reviews.rejection_reason') }}</div>
                        <p class="reviews-moderation-text">{{ $review->moderation_notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            @if($review->isPending())
                <div class="reviews-actions">
                    <a href="{{ route('reviews.edit', $review) }}" class="reviews-btn reviews-btn-secondary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('reviews.edit_review_btn') }}
                    </a>
                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" style="flex: 1;"
                          onsubmit="return confirm('{{ __('reviews.delete_confirm') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="reviews-btn reviews-btn-danger" style="width: 100%;">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('reviews.delete_review') }}
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection