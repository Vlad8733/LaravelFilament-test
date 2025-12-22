@extends('layouts.app')

@section('title', __('refunds.title'))

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="refunds-container">
        <a href="{{ route('refunds.index') }}" class="refunds-back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('refunds.back_to_requests') }}
        </a>

        @if(session('success'))
            <div class="refunds-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="refunds-alert error">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="refunds-card">
            <!-- Header -->
            <div class="refunds-card-header">
                <div class="refunds-card-header-info">
                    <h1 class="refunds-card-title">{{ __('refunds.title') }}</h1>
                    <p class="refunds-card-subtitle">{{ __('order.order_prefix', ['number' => $refund->order->order_number]) }}</p>
                </div>
                <span class="refunds-status {{ $refund->status }}">
                    <span class="refunds-status-dot"></span>
                    {{ $refund->status_label }}
                </span>
            </div>

            <!-- Details -->
            <div class="refunds-details-grid">
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">{{ __('refunds.refund_type') }}</div>
                    <div class="refunds-detail-value">{{ $refund->type === 'full' ? __('refunds.full_refund') : __('refunds.partial_refund') }}</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">{{ __('refunds.amount') }}</div>
                    <div class="refunds-detail-value large">${{ number_format($refund->amount, 2) }}</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">{{ __('refunds.submitted') }}</div>
                    <div class="refunds-detail-value">{{ $refund->created_at->translatedFormat('j F Y') }}</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">{{ __('refunds.order_total') }}</div>
                    <div class="refunds-detail-value muted">${{ number_format($refund->order->total, 2) }}</div>
                </div>
            </div>

            <!-- Reason -->
            <div class="refunds-reason">
                <div class="refunds-section-label">{{ __('refunds.reason') }}</div>
                <p class="refunds-reason-text">{{ $refund->reason }}</p>
            </div>

            <!-- Admin Notes (if any) -->
            @if($refund->admin_notes)
            <div class="refunds-admin-response">
                <div class="refunds-section-label">{{ __('refunds.admin_response') }}</div>
                <p class="refunds-admin-text">{{ $refund->admin_notes }}</p>
                @if($refund->processedBy)
                    <p class="refunds-admin-meta">
                        {{ __('refunds.by_admin', ['name' => $refund->processedBy->name, 'date' => $refund->processed_at->format('M d, Y')]) }}
                    </p>
                @endif
            </div>
            @endif

            <!-- Status History -->
            <div class="refunds-timeline">
                <div class="refunds-section-label">{{ __('refunds.status_history') }}</div>
                <div class="refunds-timeline-list">
                    @foreach($refund->statusHistory as $history)
                        <div class="refunds-timeline-item">
                            <div class="refunds-timeline-dot {{ $history->status }}"></div>
                            <div class="refunds-timeline-content">
                                <div class="refunds-timeline-header">
                                    <span class="refunds-timeline-status">{{ $history->status_label }}</span>
                                    <span class="refunds-timeline-date">{{ $history->changed_at->format('M d, Y h:i A') }}</span>
                                </div>
                                @if($history->notes)
                                    <p class="refunds-timeline-notes">{{ $history->notes }}</p>
                                @endif
                                @if($history->changedByUser)
                                    <p class="refunds-timeline-by">{{ $history->changedByUser->name }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            @if($refund->isPending())
            <div class="refunds-actions">
                <form action="{{ route('refunds.cancel', $refund) }}" method="POST" 
                      onsubmit="return confirm('{{ __('refunds.cancel_confirm') }}')">
                    @csrf
                    <button type="submit" class="refunds-btn refunds-btn-danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        {{ __('refunds.cancel_request') }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="refunds-card refunds-order-items">
            <div class="refunds-order-items-header">
                <h3 class="refunds-order-items-title">{{ __('refunds.order_items') }}</h3>
            </div>
            @foreach($refund->order->items as $item)
                <div class="refunds-order-item">
                    @if($item->product && $item->product->images->first())
                        <img src="{{ $item->product->images->first()->image_url }}" 
                             alt="{{ $item->product_name }}" 
                             class="refunds-order-item-image">
                    @else
                        <div class="refunds-order-item-placeholder">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="refunds-order-item-info">
                        <div class="refunds-order-item-name">{{ $item->product_name }}</div>
                        <div class="refunds-order-item-meta">{{ __('refunds.qty') }}: {{ $item->quantity }} × ${{ number_format($item->product_price, 2) }}</div>
                    </div>
                    <div class="refunds-order-item-price">${{ number_format($item->total, 2) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection