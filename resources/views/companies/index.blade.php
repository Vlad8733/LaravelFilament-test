@extends('layouts.app')

@section('title', __('company.all_companies'))

@push('styles')
    @vite('resources/css/pages/company.css')
@endpush

@section('content')
<div class="companies-page">
    <div class="companies-container">
        {{-- Hero Section --}}
        <div class="companies-hero">
            <div class="companies-hero__content">
                <h1 class="companies-hero__title">{{ __('company.all_companies') }}</h1>
                <p class="companies-hero__subtitle">{{ __('company.discover_sellers') }}</p>
            </div>
            <div class="companies-hero__decoration">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="100" cy="100" r="80" fill="currentColor" opacity="0.1"/>
                    <circle cx="100" cy="100" r="60" fill="currentColor" opacity="0.1"/>
                    <circle cx="100" cy="100" r="40" fill="currentColor" opacity="0.15"/>
                </svg>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="companies-toolbar">
            <form action="{{ route('companies.index') }}" method="GET" class="companies-search-form">
                <div class="companies-search-box">
                    <svg class="companies-search-icon" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: rgba(156, 163, 175, 0.8); pointer-events: none; z-index: 5;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="{{ __('company.search_placeholder') }}" 
                        class="companies-search-input"
                        style="padding-left: 3rem !important;"
                    >
                    @if(request('search'))
                        <a href="{{ route('companies.index') }}" class="companies-search-clear">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
                <div class="companies-filter-group">
                    <select name="sort" class="companies-sort-select" onchange="this.form.submit()">
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>{{ __('company.sort_name') }}</option>
                        <option value="products" {{ request('sort') === 'products' ? 'selected' : '' }}>{{ __('company.sort_products') }}</option>
                        <option value="followers" {{ request('sort') === 'followers' ? 'selected' : '' }}>{{ __('company.sort_followers') }}</option>
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>{{ __('company.sort_newest') }}</option>
                    </select>
                    <button type="submit" class="companies-search-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <span>{{ __('common.search') }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Results Count --}}
        @if($companies->count() > 0)
            <div class="companies-results-info">
                <span class="companies-count">
                    {{ $companies->total() }} {{ trans_choice('company.companies_found', $companies->total()) }}
                </span>
                @if(request('search'))
                    <span class="companies-search-term">
                        {{ __('common.for') }} "{{ request('search') }}"
                    </span>
                @endif
            </div>
        @endif

        {{-- Companies Grid --}}
        @if($companies->count() > 0)
            <div class="companies-grid">
                @foreach($companies as $company)
                    <a href="{{ route('companies.show', $company->slug) }}" class="company-card">
                        <div class="company-card__header">
                            <div class="company-card__logo">
                                @if($company->logo_url)
                                    <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" loading="lazy">
                                @else
                                    <span class="company-card__initials">
                                        {{ strtoupper(substr($company->name, 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            @if($company->is_verified)
                                <span class="company-card__verified" title="{{ __('company.verified') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0112 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 013.498 1.307 4.491 4.491 0 011.307 3.497A4.49 4.49 0 0121.75 12a4.49 4.49 0 01-1.549 3.397 4.491 4.491 0 01-1.307 3.497 4.491 4.491 0 01-3.497 1.307A4.49 4.49 0 0112 21.75a4.49 4.49 0 01-3.397-1.549 4.49 4.49 0 01-3.498-1.306 4.491 4.491 0 01-1.307-3.498A4.49 4.49 0 012.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 011.307-3.497 4.49 4.49 0 013.497-1.307zm7.007 6.387a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            @endif
                        </div>
                        
                        <div class="company-card__body">
                            <h3 class="company-card__name">{{ $company->name }}</h3>
                            @if($company->short_description)
                                <p class="company-card__description">{{ Str::limit($company->short_description, 80) }}</p>
                            @endif
                        </div>
                        
                        <div class="company-card__footer">
                            <div class="company-card__stat">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                <span>{{ $company->products_count }} {{ __('company.products') }}</span>
                            </div>
                            <div class="company-card__stat">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <span>{{ $company->followers_count }} {{ __('company.followers') }}</span>
                            </div>
                        </div>
                        
                        <div class="company-card__hover-overlay">
                            <span class="company-card__view-btn">
                                {{ __('company.view_store') }}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($companies->hasPages())
                <div class="companies-pagination">
                    {{ $companies->withQueryString()->links() }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="companies-empty">
                <div class="companies-empty__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                </div>
                <h3 class="companies-empty__title">{{ __('company.no_companies') }}</h3>
                <p class="companies-empty__text">{{ __('company.no_companies_text') }}</p>
                @if(request('search'))
                    <a href="{{ route('companies.index') }}" class="companies-empty__btn">
                        {{ __('common.clear_search') }}
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
