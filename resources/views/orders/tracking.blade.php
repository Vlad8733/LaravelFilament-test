@extends('layouts.app')

@section('title', 'Track Order - ' . $order->order_number)

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-12">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('orders.tracking.search') }}" class="back-link">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Track Another Order
        </a>
        
        <h1 class="text-4xl font-bold mb-2">Order Tracking</h1>
        <p class="text-gray-400">Order #{{ $order->order_number }}</p>
    </div>

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-500/10 border border-blue-500/20 text-blue-400 px-4 py-3 rounded-lg mb-6">
            {{ session('info') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Timeline -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Current Status Card -->
            <div class="tracking-card">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold mb-2">{{ $order->status->name }}</h2>
                        <p class="text-gray-400">{{ $order->status->description }}</p>
                    </div>
                    <div class="w-20 h-20 rounded-full flex items-center justify-center" 
                         style="background: {{ $order->status->color }}22; border: 3px solid {{ $order->status->color }}">
                        <svg class="w-10 h-10" style="color: {{ $order->status->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($order->status->slug === 'delivered')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            @elseif(in_array($order->status->slug, ['shipped', 'in-transit', 'out-for-delivery']))
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            @endif
                        </svg>
                    </div>
                </div>

                @if($order->tracking_number)
                <div class="tracking-number-box">
                    <div>
                        <div class="label">Tracking Number</div>
                        <div class="value">{{ $order->tracking_number }}</div>
                    </div>
                    <button onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); this.innerHTML='<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg>'; setTimeout(() => this.innerHTML='<svg class=\'w-6 h-6\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'></path></svg>', 2000)" 
                            class="copy-btn">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>
                </div>
                @endif
            </div>

            <!-- Timeline -->
            <div class="tracking-card">
                <h3 class="text-2xl font-bold mb-6">Order History</h3>
                
                <div class="timeline">
                    @foreach($order->statusHistory as $history)
                    <div class="timeline-item">
                        <div class="timeline-dot {{ $loop->first ? 'active' : '' }}" 
                             style="border-color: {{ $history->status->color }}"></div>
                        
                        <div class="tracking-card-small" style="margin: 0;">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="text-lg font-bold" style="color: {{ $history->status->color }}">
                                    {{ $history->status->name }}
                                </h4>
                                <span class="text-sm text-gray-400">
                                    {{ $history->changed_at->format('M d, Y') }}
                                    <br>
                                    <span class="text-xs">{{ $history->changed_at->format('H:i') }}</span>
                                </span>
                            </div>
                            <p class="text-gray-400 text-sm">{{ $history->status->description }}</p>
                            @if($history->notes)
                            <div class="mt-3 p-3 bg-black bg-opacity-30 rounded-lg border border-gray-700">
                                <p class="text-sm text-gray-300">
                                    <strong class="text-orange-400">Note:</strong> {{ $history->notes }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Refund Request Card -->
            @auth
                @php
                    $existingRefund = $order->refundRequest;
                    $canRequestRefund = in_array($order->status->slug ?? '', ['delivered', 'completed', 'shipped']);
                @endphp
                
                @if($existingRefund)
                    <div class="tracking-card-small border-2" style="border-color: 
                        @if($existingRefund->status === 'pending') #eab308
                        @elseif($existingRefund->status === 'approved') #3b82f6
                        @elseif($existingRefund->status === 'rejected') #ef4444
                        @elseif($existingRefund->status === 'completed') #22c55e
                        @endif">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: 
                                @if($existingRefund->status === 'pending') #eab308
                                @elseif($existingRefund->status === 'approved') #3b82f6
                                @elseif($existingRefund->status === 'rejected') #ef4444
                                @elseif($existingRefund->status === 'completed') #22c55e
                                @endif">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                            <h3 class="text-lg font-bold">Refund Request</h3>
                        </div>
                        <div class="space-y-2 text-sm mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status:</span>
                                <span class="font-medium px-2 py-0.5 rounded text-xs
                                    @if($existingRefund->status === 'pending') bg-yellow-500/20 text-yellow-400
                                    @elseif($existingRefund->status === 'approved') bg-blue-500/20 text-blue-400
                                    @elseif($existingRefund->status === 'rejected') bg-red-500/20 text-red-400
                                    @elseif($existingRefund->status === 'completed') bg-green-500/20 text-green-400
                                    @endif">
                                    {{ $existingRefund->status_label }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Amount:</span>
                                <span class="font-bold text-orange-400">${{ number_format($existingRefund->amount, 2) }}</span>
                            </div>
                        </div>
                        <a href="{{ route('refunds.show', $existingRefund) }}" 
                           class="block w-full py-2 px-4 bg-zinc-700 hover:bg-zinc-600 text-white text-center rounded-lg transition-colors text-sm font-medium">
                            View Details
                        </a>
                    </div>
                @elseif($canRequestRefund)
                    <div class="tracking-card-small">
                        <div class="flex items-center gap-3 mb-3">
                            <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                            </svg>
                            <h3 class="text-lg font-bold">Need a Refund?</h3>
                        </div>
                        <p class="text-gray-400 text-sm mb-4">If you're not satisfied with your order, you can request a refund.</p>
                        <a href="{{ route('refunds.create', $order) }}" 
                           class="block w-full py-2 px-4 bg-orange-500 hover:bg-orange-400 text-black text-center rounded-lg transition-colors text-sm font-medium">
                            Request Refund
                        </a>
                    </div>
                @endif
            @endauth

            <!-- Order Details -->
            <div class="tracking-card-small">
                <h3 class="text-lg font-bold mb-4">Order Details</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span class="text-gray-400">Order Date:</span>
                        <span class="font-semibold">{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-700">
                        <span class="text-gray-400">Total Amount:</span>
                        <span class="font-bold text-xl text-orange-400">${{ number_format($order->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-400">Payment Method:</span>
                        <span class="capitalize font-medium">{{ $order->payment_method }}</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="tracking-card-small">
                <h3 class="text-lg font-bold mb-4">Shipping Address</h3>
                <div class="text-sm space-y-2">
                    <p class="font-semibold text-base">{{ $order->customer_name }}</p>
                    <p class="text-gray-300 whitespace-pre-line leading-relaxed">{{ $order->shipping_address }}</p>
                    <p class="text-gray-400 pt-2 border-t border-gray-700">{{ $order->customer_email }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="tracking-card-small">
                <h3 class="text-lg font-bold mb-4">Items ({{ $order->items->count() }})</h3>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="item-card">
                        @if($item->product && $item->product->images->first())
                            <img src="{{ Storage::url($item->product->images->first()->image_path) }}" 
                                 alt="{{ $item->product_name }}"
                                 class="item-thumb"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'item-thumb-placeholder\'><svg class=\'w-8 h-8 text-gray-600\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\'></path></svg></div>';">
                        @else
                        <div class="item-thumb-placeholder">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm truncate">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400 mt-1">Quantity: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-bold text-orange-400">${{ number_format($item->total, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection