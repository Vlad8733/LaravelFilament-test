@extends('layouts.app')

@section('title', __('reviews.my_reviews'))

@push('styles')
    @vite('resources/css/reviews/reviews.css')
@endpush

@section('content')
<div class="reviews-page">
    <div class="reviews-container-wide">
        <div class="reviews-header">
            <h1 class="reviews-title">{{ __('reviews.my_reviews') }}</h1>
            <p class="reviews-subtitle">{{ __('reviews.manage_reviews') }}</p>
        </div>

        @if(session('success'))
            <div class="reviews-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="reviews-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('info') }}
            </div>
        @endif

        @if($reviews->isEmpty())
            <div class="reviews-card">
                <div class="reviews-empty">
                    <svg class="reviews-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <h3 class="reviews-empty-title">{{ __('reviews.no_reviews') }}</h3>
                    <p class="reviews-empty-text">{{ __('reviews.no_reviews_text') }}</p>
                </div>
            </div>
        @else
            <div class="reviews-list">
                @foreach($reviews as $review)
                    <a href="{{ route('reviews.show', $review) }}" class="reviews-item">
                        <div class="reviews-item-header">
                            @if($review->product && $review->product->images->first())
                                <img src="{{ $review->product->images->first()->image_url }}" 
                                     alt="{{ $review->product->name }}" 
                                     class="reviews-item-image">
                            @else
                                <div class="reviews-item-placeholder">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="reviews-item-info">
                                <div class="reviews-item-product">{{ $review->product->name ?? 'Product' }}</div>
                                <div class="reviews-item-order">{{ __('order.order_prefix', ['number' => $review->order->order_number]) }}</div>
                            </div>
                            <span class="reviews-status {{ $review->status }}">
                                <span class="reviews-status-dot"></span>
                                {{ $review->status_label }}
                            </span>
                        </div>
                        
                        <div class="reviews-item-footer">
                            <div class="reviews-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="{{ $i <= round($review->overall_rating) ? 'reviews-star-filled' : 'reviews-star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="reviews-item-date">{{ $review->created_at->translatedFormat('M d, Y') }}</span>
                        </div>

                        @if($review->comment)
                            <p class="reviews-item-comment">{{ $review->comment }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection