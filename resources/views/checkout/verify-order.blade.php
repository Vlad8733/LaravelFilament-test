@extends('layouts.app')

@section('title', 'Verify Your Order')

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full mb-6"
                 style="background: linear-gradient(135deg, #f59e0b, #fb923c); box-shadow: 0 10px 40px rgba(245, 158, 11, 0.4);">
                <svg class="w-10 h-10 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold mb-3">Verify Your Order</h1>
            <p class="text-gray-400 text-lg">Please enter your email to view order details</p>
            <p class="text-gray-500 text-sm mt-2">Order #{{ $order->order_number }}</p>
        </div>

        <div class="tracking-card">
            <form method="POST" action="{{ route('orders.verify.post', $order->id) }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        Email Address
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="your@email.com"
                           required
                           autofocus
                           class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-lg font-bold text-lg text-black transition-all transform hover:scale-[1.02] hover:shadow-2xl"
                        style="background: linear-gradient(135deg, #f59e0b, #fb923c); box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);">
                    Verify & View Order
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-700 text-center">
                <p class="text-sm text-gray-400">
                    This is a security measure to protect your order information.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection