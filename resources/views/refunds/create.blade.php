@extends('layouts.app')

@section('title', __('refunds.request_refund'))

@push('styles')
    @vite('resources/css/refunds/refunds.css')
@endpush

@section('content')
<div class="refunds-page">
    <div class="refunds-container">
        <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="refunds-back-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ __('refunds.back_to_order') }}
        </a>

        <div class="refunds-card">
            <div class="refunds-card-header">
                <div class="refunds-card-header-info">
                    <h1 class="refunds-card-title">{{ __('refunds.request_refund') }}</h1>
                    <p class="refunds-card-subtitle">{{ __('order.order_prefix', ['number' => $order->order_number]) }}</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="refunds-order-summary">
                <h3 class="refunds-order-summary-title">{{ __('refunds.order_summary') }}</h3>
                <div class="refunds-order-summary-items">
                    @foreach($order->items as $item)
                        <div class="refunds-order-summary-item">
                            <span class="refunds-order-summary-item-name">{{ $item->product_name }} × {{ $item->quantity }}</span>
                            <span class="refunds-order-summary-item-price">${{ number_format($item->total, 2) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="refunds-order-summary-total">
                    <span>{{ __('refunds.total') }}</span>
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
                    <label class="refunds-form-label">{{ __('refunds.refund_type') }}</label>
                    <div class="refunds-type-grid">
                        <label class="refunds-type-card">
                            <input type="radio" name="type" value="full" x-model="type">
                            <div class="refunds-type-card-content">
                                <div class="refunds-type-title">{{ __('refunds.full_refund') }}</div>
                                <div class="refunds-type-amount">${{ number_format($order->total, 2) }}</div>
                            </div>
                        </label>
                        <label class="refunds-type-card">
                            <input type="radio" name="type" value="partial" x-model="type">
                            <div class="refunds-type-card-content">
                                <div class="refunds-type-title">{{ __('refunds.partial_refund') }}</div>
                                <div class="refunds-type-amount">{{ __('refunds.custom_amount') }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Partial Amount -->
                <div class="refunds-form-group" x-show="type === 'partial'" x-transition>
                    <label for="amount" class="refunds-form-label">{{ __('refunds.refund_amount') }}</label>
                    <div class="refunds-input-wrapper">
                        <span class="refunds-input-prefix">$</span>
                        <input type="number" name="amount" id="amount" step="0.01" min="0.01" max="{{ $order->total }}"
                               x-model="amount"
                               class="refunds-input"
                               placeholder="0.00">
                    </div>
                    <p class="refunds-form-hint">{{ __('refunds.maximum') }}: ${{ number_format($order->total, 2) }}</p>
                </div>

                <!-- Reason -->
                <div class="refunds-form-group">
                    <label for="reason" class="refunds-form-label">{{ __('refunds.reason') }}</label>
                    <textarea name="reason" id="reason" rows="4" required minlength="10"
                              class="refunds-textarea"
                              placeholder="{{ __('refunds.reason_placeholder') }}"></textarea>
                    <p class="refunds-form-hint">{{ __('refunds.reason_hint') }}</p>
                </div>

                <!-- Submit -->
                <div class="refunds-form-actions">
                    <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="refunds-btn refunds-btn-secondary">
                        {{ __('refunds.cancel') }}
                    </a>
                    <button type="submit" class="refunds-btn refunds-btn-primary">
                        {{ __('refunds.submit_request') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection