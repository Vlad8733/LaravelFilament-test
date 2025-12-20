@extends('layouts.app')

@section('title', 'Refund Request')

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('refunds.index') }}" class="refunds-back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Refund Requests
            </a>
        </div>

        @if(session('success'))
            <div class="refunds-alert success">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="refunds-alert error">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="refunds-card">
            <!-- Header -->
            <div class="refunds-card-header">
                <div>
                    <h1 class="refunds-title" style="font-size: 1.25rem; margin-bottom: 0.25rem;">Refund Request</h1>
                    <p class="refunds-subtitle">Order #{{ $refund->order->order_number }}</p>
                </div>
                <span class="refunds-status {{ $refund->status }}">
                    <span class="refunds-status-dot"></span>
                    {{ $refund->status_label }}
                </span>
            </div>

            <!-- Details -->
            <div class="refunds-details-grid">
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">Refund Type</div>
                    <div class="refunds-detail-value" style="text-transform: capitalize;">{{ $refund->type }} Refund</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">Amount</div>
                    <div class="refunds-detail-value large">${{ number_format($refund->amount, 2) }}</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">Submitted</div>
                    <div class="refunds-detail-value">{{ $refund->created_at->format('M d, Y \a\t h:i A') }}</div>
                </div>
                <div class="refunds-detail-item">
                    <div class="refunds-detail-label">Order Total</div>
                    <div class="refunds-detail-value muted">${{ number_format($refund->order->total, 2) }}</div>
                </div>
            </div>

            <!-- Reason -->
            <div class="refunds-reason">
                <div class="refunds-section-label">Reason</div>
                <p class="refunds-reason-text">{{ $refund->reason }}</p>
            </div>

            <!-- Admin Notes (if any) -->
            @if($refund->admin_notes)
            <div class="refunds-admin-response">
                <div class="refunds-section-label">Admin Response</div>
                <p class="refunds-admin-text">{{ $refund->admin_notes }}</p>
                @if($refund->processedBy)
                    <p class="refunds-admin-meta">
                        By {{ $refund->processedBy->name }} on {{ $refund->processed_at->format('M d, Y') }}
                    </p>
                @endif
            </div>
            @endif

            <!-- Status History -->
            <div class="refunds-timeline">
                <div class="refunds-section-label" style="margin-bottom: 1rem;">Status History</div>
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
                                    <p class="refunds-timeline-by">By {{ $history->changedByUser->name }}</p>
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
                      onsubmit="return confirm('Are you sure you want to cancel this refund request?')">
                    @csrf
                    <button type="submit" class="refunds-btn refunds-btn-danger">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel Refund Request
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="refunds-card refunds-order-items">
            <div class="refunds-order-items-header">
                <h3 class="refunds-order-items-title">Order Items</h3>
            </div>
            @foreach($refund->order->items as $item)
                <div class="refunds-order-item">
                    @if($item->product && $item->product->images->first())
                        <img src="{{ $item->product->images->first()->image_url }}" 
                             alt="{{ $item->product_name }}" 
                             class="refunds-order-item-image">
                    @else
                        <div class="refunds-order-item-placeholder">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="refunds-order-item-info">
                        <div class="refunds-order-item-name">{{ $item->product_name }}</div>
                        <div class="refunds-order-item-meta">Qty: {{ $item->quantity }} × ${{ number_format($item->product_price, 2) }}</div>
                    </div>
                    <div class="refunds-order-item-price">${{ number_format($item->total, 2) }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection