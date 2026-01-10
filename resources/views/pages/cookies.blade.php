@extends('layouts.app')

@section('title', __('pages.cookies_title'))

@push('styles')
    @vite('resources/css/pages/pages.css')
@endpush

@section('content')
<div class="legal-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => __('pages.cookies_title')]
        ]" />

        <div class="legal-content">
            <h1 class="legal-title">{{ __('pages.cookies_title') }}</h1>
            <p class="legal-updated">{{ __('pages.last_updated') }}: {{ __('pages.cookies_date') }}</p>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_what_title') }}</h2>
                <p>{{ __('pages.cookies_what_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_types_title') }}</h2>
                
                <h3>{{ __('pages.cookies_essential_title') }}</h3>
                <p>{{ __('pages.cookies_essential_text') }}</p>

                <h3>{{ __('pages.cookies_functional_title') }}</h3>
                <p>{{ __('pages.cookies_functional_text') }}</p>

                <h3>{{ __('pages.cookies_analytics_title') }}</h3>
                <p>{{ __('pages.cookies_analytics_text') }}</p>

                <h3>{{ __('pages.cookies_marketing_title') }}</h3>
                <p>{{ __('pages.cookies_marketing_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_manage_title') }}</h2>
                <p>{{ __('pages.cookies_manage_text') }}</p>
                <ul>
                    <li>{{ __('pages.cookies_manage_item1') }}</li>
                    <li>{{ __('pages.cookies_manage_item2') }}</li>
                    <li>{{ __('pages.cookies_manage_item3') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_thirdparty_title') }}</h2>
                <p>{{ __('pages.cookies_thirdparty_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_updates_title') }}</h2>
                <p>{{ __('pages.cookies_updates_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.cookies_contact_title') }}</h2>
                <p>{{ __('pages.cookies_contact_text') }}</p>
                <p><a href="mailto:privacy@e-shop.com">privacy@e-shop.com</a></p>
            </section>
        </div>
    </div>
</div>
@endsection
