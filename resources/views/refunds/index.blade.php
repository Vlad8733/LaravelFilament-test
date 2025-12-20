@extends('layouts.app')

@section('title', 'My Refund Requests')

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="refunds-title">My Refund Requests</h1>
                <p class="refunds-subtitle">Track the status of your refund requests</p>
            </div>
            <a href="{{ route('orders.tracking.search') }}" class="refunds-back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Orders
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

        @if($refunds->isEmpty())
            <div class="refunds-card">
                <div class="refunds-empty">
                    <svg class="refunds-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                    <h3 class="refunds-empty-title">No refund requests yet</h3>
                    <p class="refunds-empty-text">When you request a refund for an order, it will appear here.</p>
                </div>
            </div>
        @else
            <div class="refunds-list">
                @foreach($refunds as $refund)
                    <a href="{{ route('refunds.show', $refund) }}" class="refunds-item">
                        <div class="refunds-item-header">
                            <div class="flex items-center gap-3">
                                <span class="refunds-item-order">Order #{{ $refund->order->order_number }}</span>
                                <span class="refunds-status {{ $refund->status }}">
                                    <span class="refunds-status-dot"></span>
                                    {{ $refund->status_label }}
                                </span>
                            </div>
                            <span class="refunds-item-date">{{ $refund->created_at->format('M d, Y') }}</span>
                        </div>
                        
                        <div class="refunds-item-details">
                            <span class="refunds-item-type">{{ $refund->type }} refund</span>
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