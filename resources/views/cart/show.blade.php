@extends('layouts.app')

@section('title', __('cart.title'))

@push('styles')
    @vite('resources/css/cart/show.css')
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-4xl font-bold mb-8">{{ __('cart.shopping_cart') }}</h1>

    @if($cartItems->isEmpty())
        <div class="text-center py-16">
            <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h2 class="text-2xl font-bold mb-2">{{ __('cart.empty_title') }}</h2>
            <p class="text-gray-400 mb-8">{{ __('cart.empty_subtitle') }}</p>
            <a href="{{ route('products.index') }}" class="inline-block bg-gradient-to-r from-orange-500 to-amber-500 text-black font-bold py-3 px-8 rounded-lg hover:from-orange-600 hover:to-amber-600 transition-all">
                {{ __('cart.continue_shopping') }}
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cartItems as $item)
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 border border-gray-700 flex items-center gap-6">
                    @if($item->product->images->first())
                        <img src="{{ Storage::url($item->product->images->first()->image_path) }}" 
                             alt="{{ $item->product->name }}"
                             class="w-24 h-24 object-cover rounded-lg"
                             onerror="this.src='https://via.placeholder.com/96?text=No+Image'">
                    @else
                        <div class="w-24 h-24 bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1">
                        <h3 class="text-xl font-bold mb-1">{{ $item->product->name }}</h3>
                        <p class="text-gray-400 text-sm mb-2">{{ $item->product->category->name ?? __('cart.uncategorized') }}</p>
                        <p class="text-2xl font-bold text-orange-400">${{ number_format($item->product->price, 2) }}</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" 
                                    class="w-10 h-10 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors flex items-center justify-center"
                                    {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="w-12 text-center font-bold text-lg" id="quantity-{{ $item->id }}">{{ $item->quantity }}</span>
                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" 
                                    class="w-10 h-10 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>

                        <button onclick="removeItem({{ $item->id }})" 
                                class="w-10 h-10 bg-red-500 bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors flex items-center justify-center text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-2xl p-6 border border-gray-700 sticky top-24">
                    <h2 class="text-2xl font-bold mb-6">{{ __('cart.order_summary') }}</h2>
                    
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between text-lg">
                            <span class="text-gray-400">{{ __('cart.subtotal') }}</span>
                            <span class="font-bold" id="cart-total">${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg">
                            <span class="text-gray-400">{{ __('cart.shipping') }}</span>
                            <span class="text-green-400 font-semibold">{{ __('cart.free') }}</span>
                        </div>
                        <div class="border-t border-gray-700 pt-4">
                            <div class="flex justify-between text-2xl">
                                <span class="font-bold">{{ __('cart.total') }}</span>
                                <span class="font-bold text-orange-400">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('checkout.show') }}" 
                       class="block w-full py-4 rounded-lg font-bold text-lg text-black text-center transition-all transform hover:scale-[1.02]"
                       style="background: linear-gradient(135deg, #f59e0b, #fb923c); box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);">
                        {{ __('cart.proceed_checkout') }}
                    </a>

                    <a href="{{ route('products.index') }}" 
                       class="block w-full mt-4 py-4 rounded-lg font-semibold text-center border border-gray-600 hover:bg-gray-800 transition-colors">
                        {{ __('cart.continue_shopping') }}
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    window.cartTranslations = {
        remove_confirm: '{{ __('cart.remove_confirm') }}'
    };

    function updateQuantity(itemId, newQuantity) {
        if (newQuantity < 1) return;
        
        fetch(`/cart/update/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ quantity: newQuantity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`quantity-${itemId}`).textContent = newQuantity;
                document.getElementById('cart-total').textContent = `$${data.total.toFixed(2)}`;
                location.reload();
            }
        });
    }

    function removeItem(itemId) {
        if (!confirm(window.cartTranslations.remove_confirm)) return;
        
        fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endpush
@endsection