@extends('layouts.app')

@section('title', 'Request Refund')

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="refunds-back-link">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Order
            </a>
        </div>

        <div class="refunds-card">
            <div class="refunds-card-header">
                <div>
                    <h1 class="refunds-title" style="font-size: 1.25rem; margin-bottom: 0.25rem;">Request a Refund</h1>
                    <p class="refunds-subtitle">Order #{{ $order->order_number }}</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="refunds-order-summary">
                <h3 class="refunds-order-summary-title">Order Summary</h3>
                <div class="refunds-order-summary-items">
                    @foreach($order->items as $item)
                        <div class="refunds-order-summary-item">
                            <span class="refunds-order-summary-item-name">{{ $item->product_name }} × {{ $item->quantity }}</span>
                            <span class="refunds-order-summary-item-price">${{ number_format($item->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="refunds-order-summary-total">
                    <span>Total</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Refund Form -->
            <form action="{{ route('refunds.store', $order) }}" method="POST" class="refunds-form" x-data="{ type: 'full', amount: {{ $order->total }} }">
                @csrf

                @if($errors->any())
                    <div class="refunds-errors">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Refund Type -->
                <div class="refunds-form-group">
                    <label class="refunds-form-label">Refund Type</label>
                    <div class="refunds-type-grid">
                        <label class="refunds-type-card">
                            <input type="radio" name="type" value="full" x-model="type">
                            <div class="refunds-type-card-content">
                                <div class="refunds-type-title">Full Refund</div>
                                <div class="refunds-type-amount">${{ number_format($order->total, 2) }}</div>
                            </div>
                        </label>
                        <label class="refunds-type-card">
                            <input type="radio" name="type" value="partial" x-model="type">
                            <div class="refunds-type-card-content">
                                <div class="refunds-type-title">Partial Refund</div>
                                <div class="refunds-type-amount">Custom amount</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Partial Amount -->
                <div class="refunds-form-group" x-show="type === 'partial'" x-transition>
                    <label for="amount" class="refunds-form-label">Refund Amount</label>
                    <div class="refunds-input-wrapper">
                        <span class="refunds-input-prefix">$</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" max="{{ $order->total }}"
                               x-model="amount"
                               class="refunds-input"
                               placeholder="0.00">
                    </div>
                    <p class="refunds-form-hint">Maximum: ${{ number_format($order->total, 2) }}</p>
                </div>

                <!-- Reason -->
                <div class="refunds-form-group">
                    <label for="reason" class="refunds-form-label">Reason for Refund</label>
                    <textarea name="reason" id="reason" rows="4" required minlength="10"
                              class="refunds-textarea"
                              placeholder="Please explain why you're requesting a refund..."></textarea>
                    <p class="refunds-form-hint">Minimum 10 characters</p>
                </div>

                <!-- Submit -->
                <div class="refunds-form-actions">
                    <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="refunds-btn refunds-btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="refunds-btn refunds-btn-primary">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection