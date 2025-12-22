@extends('layouts.app')

@section('title', __('order.verify_email_title'))

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="tracking-auth-page">
    <div class="tracking-auth-container">
        <div class="tracking-header-centered">
            <div class="tracking-icon blue">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="tracking-title">{{ __('order.verify_email_title') }}</h1>
            <p class="tracking-subtitle">{{ __('order.verify_email_subtitle') }}</p>
        </div>

        <div class="tracking-card">
            <form method="GET" action="{{ route('orders.track', $orderNumber) }}" class="tracking-form">
                <div class="form-group">
                    <label class="form-label">{{ __('order.email_address') }}</label>
                    <input type="email" 
                           name="email" 
                           placeholder="{{ __('order.email_placeholder') }}"
                           required
                           class="form-input blue">
                </div>

                <button type="submit" class="btn-primary blue">
                    {{ __('order.continue') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection