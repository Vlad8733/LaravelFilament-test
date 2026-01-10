@extends('layouts.app')

@section('title', __('pages.privacy_title'))

@push('styles')
    @vite('resources/css/pages/pages.css')
@endpush

@section('content')
<div class="legal-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => __('pages.privacy_title')]
        ]" />

        <div class="legal-content">
            <h1 class="legal-title">{{ __('pages.privacy_title') }}</h1>
            <p class="legal-updated">{{ __('pages.last_updated') }}: {{ __('pages.privacy_date') }}</p>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_intro_title') }}</h2>
                <p>{{ __('pages.privacy_intro_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_collect_title') }}</h2>
                <p>{{ __('pages.privacy_collect_text') }}</p>
                <ul>
                    <li>{{ __('pages.privacy_collect_item1') }}</li>
                    <li>{{ __('pages.privacy_collect_item2') }}</li>
                    <li>{{ __('pages.privacy_collect_item3') }}</li>
                    <li>{{ __('pages.privacy_collect_item4') }}</li>
                    <li>{{ __('pages.privacy_collect_item5') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_use_title') }}</h2>
                <p>{{ __('pages.privacy_use_text') }}</p>
                <ul>
                    <li>{{ __('pages.privacy_use_item1') }}</li>
                    <li>{{ __('pages.privacy_use_item2') }}</li>
                    <li>{{ __('pages.privacy_use_item3') }}</li>
                    <li>{{ __('pages.privacy_use_item4') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_share_title') }}</h2>
                <p>{{ __('pages.privacy_share_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_security_title') }}</h2>
                <p>{{ __('pages.privacy_security_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_rights_title') }}</h2>
                <p>{{ __('pages.privacy_rights_text') }}</p>
                <ul>
                    <li>{{ __('pages.privacy_rights_item1') }}</li>
                    <li>{{ __('pages.privacy_rights_item2') }}</li>
                    <li>{{ __('pages.privacy_rights_item3') }}</li>
                    <li>{{ __('pages.privacy_rights_item4') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.privacy_contact_title') }}</h2>
                <p>{{ __('pages.privacy_contact_text') }}</p>
                <p><a href="mailto:privacy@e-shop.com">privacy@e-shop.com</a></p>
            </section>
        </div>
    </div>
</div>
@endsection
