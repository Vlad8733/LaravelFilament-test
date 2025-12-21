@extends('layouts.app')

@section('title', __('order.track_title'))

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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold mb-3">{{ __('order.track_title') }}</h1>
            <p class="text-gray-400 text-lg">{{ __('order.track_subtitle') }}</p>
        </div>

        <div class="tracking-card">
            <form method="POST" action="{{ route('orders.tracking.search.post') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        {{ __('order.order_number') }}
                    </label>
                    <input type="text" 
                           name="order_number" 
                           value="{{ old('order_number') }}"
                           placeholder="{{ __('order.order_number_placeholder') }}"
                           required
                           class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    @error('order_number')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        {{ __('order.email_address') }}
                    </label>
                    <input type="email" 
                           name="email" 
                           value="{{ old('email') }}"
                           placeholder="{{ __('order.email_placeholder') }}"
                           required
                           class="w-full px-4 py-3 bg-black bg-opacity-30 border border-gray-600 rounded-lg text-white placeholder-gray-500 focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" 
                        class="w-full py-4 rounded-lg font-bold text-lg text-black transition-all transform hover:scale-[1.02] hover:shadow-2xl"
                        style="background: linear-gradient(135deg, #f59e0b, #fb923c); box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);">
                    {{ __('order.track_button') }}
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-700 text-center">
                <p class="text-sm text-gray-400">
                    {{ __('order.need_help') }} <a href="{{ route('tickets.index') }}" class="text-orange-400 hover:text-orange-300 font-semibold">{{ __('order.contact_support') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection