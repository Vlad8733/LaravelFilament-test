<!-- filepath: /Users/Temnoy/Documents/filament-test/resources/views/checkout/success.blade.php -->
@extends('layouts.app')

@section('title','Order Success - My Shop')

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
        
        <h1 class="text-2xl font-bold mb-4">Order Placed Successfully!</h1>
        
        <div class="success-details">
            <p class="text-lg mb-2">
                Thank you, <strong>{{ $order->customer_name }}</strong>!
            </p>
            <p class="text-gray-400 mb-4">
                Your order has been received and is being processed.
            </p>
            <div class="space-y-2 text-sm">
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
                <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                <p><strong>Status:</strong> <span class="text-green-600 font-semibold">{{ ucfirst($order->order_status) }}</span></p>
            </div>
        </div>
        
        <div class="space-y-3 mt-6">
            <p class="text-gray-400">
                We'll send you a confirmation email with tracking information.
            </p>
            <a href="{{ route('products.index') }}" 
               class="btn-continue">
                Continue Shopping
            </a>

            <a href="{{ route('orders.tracking.show', ['orderNumber' => $order->order_number]) }}" 
               class="inline-block bg-gray-700 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors mt-3">
                Track Order
            </a>
        </div>
    </div>
</div>
@endsection