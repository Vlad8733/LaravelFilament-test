@extends('layouts.app')

@section('title', __('settings.title'))

@push('styles')
<style>
    .settings-page {
        min-height: 100vh;
        padding: 2rem 0;
    }

    .settings-container {
        max-width: 640px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .settings-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #f59e0b;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        margin-bottom: 1.5rem;
        transition: all 0.2s;
    }

    .settings-back:hover {
        color: #fbbf24;
        transform: translateX(-4px);
    }

    .settings-header {
        margin-bottom: 2rem;
    }

    .settings-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #e5e7eb;
        margin-bottom: 0.5rem;
    }

    .settings-subtitle {
        color: #9ca3af;
        font-size: 0.9375rem;
    }

    .settings-card {
        background: linear-gradient(180deg, #1a1a1a, #141414);
        border: 1px solid #2a2a2a;
        border-radius: 16px;
        overflow: hidden;
    }

    .settings-section {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .settings-section:last-child {
        border-bottom: none;
    }

    .settings-section-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .settings-section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(245, 158, 11, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f59e0b;
    }

    .settings-section-icon svg {
        width: 20px;
        height: 20px;
    }

    .settings-section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #e5e7eb;
    }

    .settings-section-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 1rem;
    }

    .settings-select {
        width: 100%;
        padding: 0.875rem 1rem;
        background: #0f0f0f;
        border: 1px solid #2a2a2a;
        border-radius: 10px;
        color: #e5e7eb;
        font-size: 0.9375rem;
        cursor: pointer;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1.25rem;
    }

    .settings-select:hover {
        border-color: rgba(245, 158, 11, 0.3);
    }

    .settings-select:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }

    .settings-select option {
        background: #1a1a1a;
        color: #e5e7eb;
        padding: 0.5rem;
    }

    .settings-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: #000;
        font-weight: 600;
        font-size: 0.9375rem;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 1rem;
    }

    .settings-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
    }

    .settings-btn svg {
        width: 18px;
        height: 18px;
    }

    .settings-alert {
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9375rem;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.2);
        color: #4ade80;
    }

    .settings-alert svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
    }

    .language-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .language-flag {
        font-size: 1.25rem;
    }
</style>
@endpush

@section('content')
<div class="settings-page">
    <div class="settings-container">
        <!-- Back Link -->
        <a href="{{ route('profile.edit') }}" class="settings-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
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
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Settings Card -->
        <div class="settings-card">
            <!-- Language Section -->
            <div class="settings-section">
                <div class="settings-section-header">
                    <div class="settings-section-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                    </div>
                    <h3 class="settings-section-title">{{ __('settings.language') }}</h3>
                </div>
                
                <p class="settings-section-description">{{ __('settings.language_description') }}</p>
                
                <form action="{{ route('settings.locale') }}" method="POST">
                    @csrf
                    <select name="locale" class="settings-select">
                        <option value="en" {{ auth()->user()->locale === 'en' ? 'selected' : '' }}>
                            🇺🇸 {{ __('settings.english') }}
                        </option>
                        <option value="ru" {{ auth()->user()->locale === 'ru' ? 'selected' : '' }}>
                            🇷🇺 {{ __('settings.russian') }}
                        </option>
                    </select>
                    
                    <button type="submit" class="settings-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('settings.save') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection