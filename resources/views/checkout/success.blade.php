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
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="success-card">
        <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold mb-4">{{ __('checkout.order_placed') }}</h1>
        
        <div class="success-details">
            <p class="text-lg mb-2">
                {{ __('checkout.thank_you', ['name' => $order->customer_name]) }}
            </p>
            <p class="text-gray-400 mb-4">
                {{ __('checkout.order_received') }}
            </p>
            <div class="space-y-2 text-sm">
                <p><strong>{{ __('checkout.order_number') }}:</strong> {{ $order->order_number }}</p>
                <p><strong>{{ __('checkout.total') }}:</strong> ${{ number_format($order->total, 2) }}</p>
                <p><strong>{{ __('checkout.email') }}:</strong> {{ $order->customer_email }}</p>
                <p><strong>{{ __('checkout.status') }}:</strong> <span class="text-green-600 font-semibold">{{ ucfirst($order->order_status) }}</span></p>
            </div>
        </div>
        
        <div class="space-y-3 mt-6">
            <p class="text-gray-400">
                {{ __('checkout.confirmation_email') }}
            </p>
            <a href="{{ route('products.index') }}" 
               class="btn-continue">
                {{ __('checkout.continue_shopping') }}
            </a>

            <a href="{{ route('orders.tracking.show', ['orderNumber' => $order->order_number]) }}" 
               class="inline-block bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors mt-3">
                {{ __('checkout.track_order') }}
            </a>
        </div>
    </div>
</div>
@endsection