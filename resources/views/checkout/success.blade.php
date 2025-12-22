<!-- filepath: /Users/Temnoy/Documents/filament-test/resources/views/checkout/success.blade.php -->
@extends('layouts.app')

@section('title', __('checkout.success_title'))

@push('styles')
    @vite('resources/css/checkout/success.css')
@endpush

@push('scripts')
    @vite('resources/js/checkout/success.js')
@endpush

@section('content')
<div class="success-page">
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="success-title">{{ __('checkout.order_placed') }}</h1>
            <p class="success-subtitle">{{ __('checkout.thank_you', ['name' => $order->customer_name]) }}</p>
            <p class="success-message">{{ __('checkout.order_received') }}</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">{{ __('checkout.order_number') }}</span>
                    <span class="detail-value highlight">{{ $order->order_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('checkout.total') }}</span>
                    <span class="detail-value">${{ number_format($order->total, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('checkout.email') }}</span>
                    <span class="detail-value">{{ $order->customer_email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">{{ __('checkout.status') }}</span>
                    <span class="status-badge">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ ucfirst($order->order_status) }}
                    </span>
                </div>
            </div>
            
            <div class="email-notice">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p>{{ __('checkout.confirmation_email') }}</p>
            </div>
            
            <div class="success-actions">
                <a href="{{ route('products.index') }}" class="btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    {{ __('checkout.continue_shopping') }}
                </a>
                <a href="{{ route('orders.tracking.show', ['orderNumber' => $order->order_number]) }}" class="btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    {{ __('checkout.track_order') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection