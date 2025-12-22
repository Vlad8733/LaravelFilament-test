@extends('layouts.app')

@section('title', __('refunds.my_requests'))

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="refunds-container">
        <div class="refunds-header">
            <div class="refunds-header-info">
                <h1 class="refunds-title">{{ __('refunds.my_requests') }}</h1>
                <p class="refunds-subtitle">{{ __('refunds.track_status') }}</p>
            </div>
            <a href="{{ route('orders.tracking.search') }}" class="refunds-back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ __('refunds.back_to_orders') }}
            </a>
        </div>

        @if(session('success'))
            <div class="refunds-alert success">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if($refunds->isEmpty())
            <div class="refunds-card">
                <div class="refunds-empty">
                    <svg class="refunds-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                    <h3 class="refunds-empty-title">{{ __('refunds.no_requests') }}</h3>
                    <p class="refunds-empty-text">{{ __('refunds.no_requests_text') }}</p>
                </div>
            </div>
        @else
            <div class="refunds-list">
                @foreach($refunds as $refund)
                    <a href="{{ route('refunds.show', $refund) }}" class="refunds-item">
                        <div class="refunds-item-header">
                            <div class="refunds-item-info">
                                <span class="refunds-item-order">{{ __('order.order_prefix', ['number' => $refund->order->order_number]) }}</span>
                                <span class="refunds-status {{ $refund->status }}">
                                    <span class="refunds-status-dot"></span>
                                    {{ $refund->status_label }}
                                </span>
                            </div>
                            <span class="refunds-item-date">{{ $refund->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="refunds-item-details">
                            <span class="refunds-item-type">{{ $refund->type === 'full' ? __('refunds.full_refund') : __('refunds.partial_refund') }}</span>
                            <span class="refunds-item-amount">${{ number_format($refund->amount, 2) }}</span>
                        </div>
                        
                        <p class="refunds-item-reason">{{ $refund->reason }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection