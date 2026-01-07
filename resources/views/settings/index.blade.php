@extends('layouts.app')

@section('title', __('settings.title'))

@push('styles')
    @vite('resources/css/settings/settings.css')
@endpush

@section('content')
<div class="settings-page">
    <div class="settings-container">
        <!-- Back Link -->
        <a href="{{ route('profile.edit') }}" class="settings-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ __('settings.back_to_profile') }}
        </a>

        <!-- Header -->
        <div class="settings-header">
            <h1 class="settings-title">{{ __('settings.title') }}</h1>
            <p class="settings-subtitle">{{ __('settings.subtitle') }}</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="settings-alert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Layout Grid -->
        <div class="settings-layout">
            <!-- Sidebar Navigation -->
            <aside class="settings-sidebar">
                <nav class="settings-nav">
                    <!-- General -->
                    <div class="settings-nav-group">
                        <span class="settings-nav-group-title">{{ __('settings.general') }}</span>
                        
                        <button type="button" class="settings-nav-item active" data-panel="language">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                            {{ __('settings.nav_language') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="appearance">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            {{ __('settings.nav_appearance') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="notifications">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            {{ __('settings.nav_notifications') }}
                        </button>
                    </div>
                    
                    <!-- Security -->
                    <div class="settings-nav-group">
                        <span class="settings-nav-group-title">{{ __('settings.security') }}</span>
                        
                        <button type="button" class="settings-nav-item" data-panel="security">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            {{ __('settings.nav_security') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="login-history">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ __('settings.nav_login_history') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="social-accounts">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            {{ __('settings.nav_social_accounts') }}
                        </button>
                    </div>
                    
                    <!-- Billing -->
                    <div class="settings-nav-group">
                        <span class="settings-nav-group-title">{{ __('settings.billing') }}</span>
                        
                        <button type="button" class="settings-nav-item" data-panel="orders">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            {{ __('settings.nav_orders') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="addresses">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('settings.nav_addresses') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="payment-methods">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            {{ __('settings.nav_payment_methods') }}
                        </button>
                    </div>
                    
                    <!-- Subscriptions -->
                    <div class="settings-nav-group">
                        <span class="settings-nav-group-title">{{ __('settings.subscriptions') }}</span>
                        
                        <button type="button" class="settings-nav-item" data-panel="followed-companies">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ __('settings.nav_followed_companies') }}
                        </button>
                        
                        <button type="button" class="settings-nav-item" data-panel="newsletter">
                            <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ __('settings.nav_newsletter') }}
                        </button>
                    </div>
                    
                    <!-- Links -->
                    <div class="settings-nav-divider"></div>
                    
                    <a href="{{ route('analytics.index') }}" class="settings-nav-item settings-nav-link">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ __('settings.nav_analytics') }}
                        <svg class="settings-nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </nav>
            </aside>

            <!-- Content Area -->
            <div class="settings-content">
                @include('settings.panels.language')
                @include('settings.panels.appearance')
                @include('settings.panels.notifications')
                @include('settings.panels.security')
                @include('settings.panels.login-history')
                @include('settings.panels.social-accounts')
                @include('settings.panels.orders')
                @include('settings.panels.addresses')
                @include('settings.panels.payment-methods')
                @include('settings.panels.followed-companies')
                @include('settings.panels.newsletter')
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('settings.modals.address-modal')
@include('settings.modals.payment-modal')
@endsection

@push('scripts')
@include('settings.scripts')
@endpush
