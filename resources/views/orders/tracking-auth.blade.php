@extends('layouts.app')

@section('title', 'Verify Email')

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6"
                 style="background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 10px 40px rgba(59, 130, 246, 0.4);">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold mb-3">Verify Your Email</h1>
            <p class="text-gray-400 text-lg">Please enter your email to view order details</p>
        </div>

        <div class="tracking-card">
            <form method="GET" action="{{ route('orders.track', $orderNumber) }}" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           placeholder="your@email.com"
                           required
                           class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-lg font-bold text-lg text-white transition-all transform hover:scale-[1.02]"
                        style="background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);">
                    Continue
                </button>
            </form>
        </div>
    </div>
</div>
@endsection