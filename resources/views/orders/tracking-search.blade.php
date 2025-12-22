@extends('layouts.app')

@section('title', __('order.track_title'))

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="tracking-search-page">
    <div class="tracking-auth-container">
        <div class="tracking-header-centered">
            <div class="tracking-icon accent">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h1 class="tracking-title">{{ __('order.track_title') }}</h1>
            <p class="tracking-subtitle">{{ __('order.track_subtitle') }}</p>
        </div>

        <div class="tracking-card">
            <form method="POST" action="{{ route('orders.tracking.search.post') }}" class="tracking-form">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">{{ __('order.order_number') }}</label>
                    <input type="text" 
                           name="order_number" 
                           value="{{ old('order_number') }}"
                           placeholder="{{ __('order.order_number_placeholder') }}"
                           required
                           class="form-input @error('order_number') error @enderror">
                    @error('order_number')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('order.email_address') }}</label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="{{ __('order.email_placeholder') }}"
                           required
                           class="form-input @error('email') error @enderror">
                    @error('email')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    {{ __('order.track_button') }}
                </button>
            </form>

            <div class="form-footer">
                <p>
                    {{ __('order.need_help') }} <a href="{{ route('tickets.index') }}">{{ __('order.contact_support') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection