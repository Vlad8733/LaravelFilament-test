@extends('layouts.app')

@section('title', __('checkout.verify_title'))

@push('styles')
    @vite('resources/css/checkout/success.css')
@endpush

@section('content')
<div class="verify-page">
    <div class="verify-container">
        <div class="verify-header">
            <div class="verify-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="verify-title">{{ __('checkout.verify_title') }}</h1>
            <p class="verify-subtitle">{{ __('checkout.verify_subtitle') }}</p>
            <p class="verify-order-number">{{ __('checkout.verify_order_number', ['number' => $order->order_number]) }}</p>
        </div>

        <div class="verify-card">
            <form method="POST" action="{{ route('orders.verify.post', $order->id) }}" class="verify-form">
                @csrf
                
                <div class="verify-input-group">
                    <label>{{ __('checkout.email_address') }}</label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="{{ __('checkout.email_placeholder') }}"
                           required
                           autofocus
                           class="verify-input @error('email') error @enderror">
                    @error('email')
                        <p class="verify-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="verify-submit">
                    {{ __('checkout.verify_button') }}
                </button>
            </form>

            <div class="verify-footer">
                <p>{{ __('checkout.verify_security_note') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection