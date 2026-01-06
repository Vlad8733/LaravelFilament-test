@extends('layouts.app')

@section('title', __('pages.terms_title'))

@push('styles')
    @vite('resources/css/pages/pages.css')
@endpush

@section('content')
<div class="legal-page">
    <div class="container">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => __('pages.terms_title')]
        ]" />

        <div class="legal-content">
            <h1 class="legal-title">{{ __('pages.terms_title') }}</h1>
            <p class="legal-updated">{{ __('pages.last_updated') }}: {{ __('pages.terms_date') }}</p>

            <section class="legal-section">
                <h2>{{ __('pages.terms_acceptance_title') }}</h2>
                <p>{{ __('pages.terms_acceptance_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_account_title') }}</h2>
                <p>{{ __('pages.terms_account_text') }}</p>
                <ul>
                    <li>{{ __('pages.terms_account_item1') }}</li>
                    <li>{{ __('pages.terms_account_item2') }}</li>
                    <li>{{ __('pages.terms_account_item3') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_orders_title') }}</h2>
                <p>{{ __('pages.terms_orders_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_payment_title') }}</h2>
                <p>{{ __('pages.terms_payment_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_shipping_title') }}</h2>
                <p>{{ __('pages.terms_shipping_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_returns_title') }}</h2>
                <p>{{ __('pages.terms_returns_text') }}</p>
                <ul>
                    <li>{{ __('pages.terms_returns_item1') }}</li>
                    <li>{{ __('pages.terms_returns_item2') }}</li>
                    <li>{{ __('pages.terms_returns_item3') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_prohibited_title') }}</h2>
                <p>{{ __('pages.terms_prohibited_text') }}</p>
                <ul>
                    <li>{{ __('pages.terms_prohibited_item1') }}</li>
                    <li>{{ __('pages.terms_prohibited_item2') }}</li>
                    <li>{{ __('pages.terms_prohibited_item3') }}</li>
                    <li>{{ __('pages.terms_prohibited_item4') }}</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_liability_title') }}</h2>
                <p>{{ __('pages.terms_liability_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_changes_title') }}</h2>
                <p>{{ __('pages.terms_changes_text') }}</p>
            </section>

            <section class="legal-section">
                <h2>{{ __('pages.terms_contact_title') }}</h2>
                <p>{{ __('pages.terms_contact_text') }}</p>
                <p><a href="mailto:legal@shoply.com">legal@shoply.com</a></p>
            </section>
        </div>
    </div>
</div>
@endsection
