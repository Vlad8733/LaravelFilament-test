@extends('layouts.app')

@section('title', __('order.tracking_title') . ' - ' . $order->order_number)

@push('styles')
    @vite('resources/css/orders/tracking.css')
@endpush

@section('content')
<div class="tracking-page">
    <div class="tracking-container">
        <!-- Header -->
        <div class="tracking-header">
            <a href="{{ route('orders.tracking.search') }}" class="back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                {{ __('order.track_another') }}
            </a>
            
            <h1 class="tracking-title">{{ __('order.order_tracking') }}</h1>
            <p class="tracking-subtitle">{{ __('order.order_prefix', ['number' => $order->order_number]) }}</p>
        </div>

        @if(session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if(session('info'))
            <div class="alert info">{{ session('info') }}</div>
        @endif

        <div class="tracking-grid">
            <!-- Main Timeline -->
            <div class="tracking-main">
                <!-- Current Status Card -->
                <div class="tracking-card">
                    <div class="status-card">
                        <div class="status-info">
                            <h2 style="color: {{ $order->status->color }}">{{ $order->status->translated_name }}</h2>
                            <p>{{ $order->status->translated_description }}</p>
                        </div>
                        <div class="status-icon" style="background: {{ $order->status->color }}15; border-color: {{ $order->status->color }}">
                            <svg style="color: {{ $order->status->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="label">{{ __('order.tracking_number') }}</div>
                            <div class="value">{{ $order->tracking_number }}</div>
                        </div>
                        <button onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); this.innerHTML='<svg fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M5 13l4 4L19 7\'></path></svg>'; setTimeout(() => this.innerHTML='<svg fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'></path></svg>', 2000)" 
                                class="copy-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Timeline -->
                <div class="tracking-card">
                    <h3 class="card-title-lg">{{ __('order.order_history') }}</h3>
                    
                    <div class="timeline">
                        @foreach($order->statusHistory as $history)
                        <div class="timeline-item">
                            <div class="timeline-dot {{ $loop->first ? 'active' : '' }}" 
                                 style="border-color: {{ $history->status->color }}; {{ $loop->first ? 'background: ' . $history->status->color . ';' : '' }}"></div>
                            
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="timeline-status" style="color: {{ $history->status->color }}">
                                        {{ $history->status->translated_name }}
                                    </span>
                                    <span class="timeline-date">
                                        {{ $history->changed_at->format('M d, Y') }} · {{ $history->changed_at->format('H:i') }}
                                    </span>
                                </div>
                                <p class="timeline-description">{{ $history->status->translated_description }}</p>
                                @if($history->notes)
                                <div class="timeline-notes">
                                    <p><strong>{{ __('order.note') }}:</strong> {{ $history->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="tracking-sidebar">
                <!-- Refund Request Card -->
                @auth
                    @php
                        $existingRefund = $order->refundRequest;
                        $canRequestRefund = in_array($order->status->slug ?? '', ['delivered', 'completed', 'shipped']);
                    @endphp
                    
                    @if($existingRefund)
                        <div class="tracking-card-small refund-card {{ $existingRefund->status }}">
                            <div class="card-header">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: 
                                    @if($existingRefund->status === 'pending') var(--warning)
                                    @elseif($existingRefund->status === 'approved') var(--blue)
                                    @elseif($existingRefund->status === 'rejected') var(--error)
                                    @elseif($existingRefund->status === 'completed') var(--success)
                                    @endif">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                </svg>
                                <h3 class="card-title">{{ __('order.refund_request') }}</h3>
                            </div>
                            <div class="detail-list" style="margin-bottom: 16px;">
                                <div class="detail-row">
                                    <span class="detail-label">{{ __('order.refund_status') }}</span>
                                    <span class="status-badge {{ $existingRefund->status }}">
                                        {{ $existingRefund->status_label }}
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">{{ __('order.refund_amount') }}</span>
                                    <span class="detail-value highlight">${{ number_format($existingRefund->amount, 2) }}</span>
                                </div>
                            </div>
                            <a href="{{ route('refunds.show', $existingRefund) }}" class="btn-secondary">
                                {{ __('order.view_details') }}
                            </a>
                        </div>
                    @elseif($canRequestRefund)
                        <div class="tracking-card-small">
                            <div class="card-header">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                </svg>
                                <h3 class="card-title">{{ __('order.need_refund') }}</h3>
                            </div>
                            <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 16px;">{{ __('order.refund_description') }}</p>
                            <a href="{{ route('refunds.create', $order) }}" class="btn-action accent">
                                {{ __('order.request_refund') }}
                            </a>
                        </div>
                    @endif
                @endauth

                <!-- Leave Review Card -->
                @auth
                    @if($order->canBeReviewed())
                        @php
                            $existingReviews = $order->reviews()->where('user_id', auth()->id())->pluck('product_id')->toArray();
                            $itemsToReview = $order->items->filter(fn($item) => !in_array($item->product_id, $existingReviews));
                        @endphp
                        
                        @if($itemsToReview->isNotEmpty())
                            <div class="tracking-card-small review-card">
                                <div class="card-header">
                                    <svg fill="currentColor" viewBox="0 0 20 20" style="width: 24px; height: 24px; color: var(--warning);">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <h3 class="card-title">{{ __('order.share_experience') }}</h3>
                                </div>
                                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 16px;">
                                    {{ __('order.review_description', ['count' => $itemsToReview->count(), 'products' => $itemsToReview->count() == 1 ? __('order.product') : __('order.products')]) }}
                                </p>
                                <a href="{{ route('reviews.create', $order) }}" class="btn-action yellow">
                                    {{ __('order.leave_review') }}
                                </a>
                            </div>
                        @else
                            <div class="tracking-card-small">
                                <div class="card-header">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--success);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <h3 class="card-title">{{ __('order.thanks_reviews') }}</h3>
                                </div>
                                <p style="color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 16px;">{{ __('order.reviewed_all') }}</p>
                                <a href="{{ route('reviews.index') }}" class="btn-secondary">
                                    {{ __('order.view_my_reviews') }}
                                </a>
                            </div>
                        @endif
                    @endif
                @endauth

                <!-- Order Details -->
                <div class="tracking-card-small">
                    <h3 class="card-title" style="margin-bottom: 16px;">{{ __('order.order_details') }}</h3>
                    <div class="detail-list">
                        <div class="detail-row">
                            <span class="detail-label">{{ __('order.order_date') }}</span>
                            <span class="detail-value">{{ $order->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ __('order.total_amount') }}</span>
                            <span class="detail-value highlight">${{ number_format($order->total, 2) }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">{{ __('order.payment_method') }}</span>
                            <span class="detail-value" style="text-transform: capitalize;">{{ $order->payment_method }}</span>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="tracking-card-small">
                    <h3 class="card-title" style="margin-bottom: 16px;">{{ __('order.shipping_address') }}</h3>
                    <p class="address-name">{{ $order->customer_name }}</p>
                    <p class="address-text">{{ $order->shipping_address }}</p>
                    <p class="address-email">{{ $order->customer_email }}</p>
                </div>

                <!-- Order Items -->
                <div class="tracking-card-small">
                    <h3 class="card-title" style="margin-bottom: 16px;">{{ __('order.items') }} ({{ $order->items->count() }})</h3>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($order->items as $item)
                        <div class="item-card">
                            @if($item->product && $item->product->images->first())
                                <img src="{{ Storage::url($item->product->images->first()->image_path) }}" 
                                     alt="{{ $item->product_name }}"
                                     class="item-thumb"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'item-thumb-placeholder\'><svg fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4\'></path></svg></div>';">
                            @else
                            <div class="item-thumb-placeholder">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            @endif
                            <div class="item-info">
                                <p class="item-name">{{ $item->product_name }}</p>
                                <p class="item-qty">{{ __('order.quantity') }}: {{ $item->quantity }}</p>
                            </div>
                            <span class="item-price">${{ number_format($item->total, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection