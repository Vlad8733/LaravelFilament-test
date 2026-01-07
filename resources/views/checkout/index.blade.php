@extends('layouts.app')

@section('title', __('checkout.title') . ' - My Shop')

@push('styles')
    @vite('resources/css/checkout/checkoutindex.css')
@endpush

@push('scripts')
    @vite('resources/js/checkout/checkoutindex.js')
@endpush

@section('content')

<div class="checkout-page">
    <div class="checkout-container">
        <!-- Page Header -->
        <header class="checkout-header">
            <h1>{{ __('checkout.checkout') }}</h1>
        </header>

        @if($cartItems->isEmpty())
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <h2>{{ __('checkout.cart_empty') }}</h2>
                <p>{{ __('checkout.cart_empty_desc') ?? 'Добавьте товары в корзину для оформления заказа' }}</p>
                <a href="{{ route('products.index') }}" class="btn-primary" style="display: inline-flex; width: auto;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ __('checkout.continue_shopping') }}
                </a>
            </div>
        @else
            <form id="checkoutForm">
                @csrf
                <div class="checkout-grid">
                    <!-- Order Summary (Left Column) -->
                    <div class="checkout-card">
                        <h2 class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ __('checkout.order_summary') }}
                        </h2>
                        
                        <div class="summary-items">
                            @foreach($cartItems as $item)
                                <div class="summary-item">
                                    <div class="summary-thumb">
                                        @if($item->product->images->first())
                                            <img src="{{ Storage::url($item->product->images->first()->image_path) }}" 
                                                 alt="{{ $item->product->name }}">
                                        @else
                                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">
                                                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="summary-details">
                                        <div class="summary-name">{{ $item->product->name }}</div>
                                        @if($item->variant)
                                            @php
                                                $v = $item->variant;
                                                $attrs = is_array($v->attributes) ? collect($v->attributes)->map(fn($val,$k) => "$k: $val")->join(', ') : null;
                                                $variantLabel = $attrs ?: ($v->sku ?? '');
                                            @endphp
                                            @if($variantLabel)
                                                <div class="summary-meta text-gray-500">{{ $variantLabel }}</div>
                                            @endif
                                        @endif
                                        <div class="summary-meta">{{ __('checkout.qty') }}: {{ $item->quantity }}</div>
                                    </div>
                                    @php
                                        $priceSource = $item->variant ?? $item->product;
                                        $unitPrice = $priceSource->sale_price ?? $priceSource->price ?? 0;
                                    @endphp
                                    <div class="summary-price">${{ number_format($unitPrice * $item->quantity, 2) }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="summary-totals">
                            <div class="summary-row">
                                <span class="label">{{ __('checkout.subtotal') }}</span>
                                <span class="value">${{ number_format($subtotal, 2) }}</span>
                            </div>
                            @if($discount > 0)
                                <div class="summary-row discount">
                                    <span class="label">{{ __('checkout.discount') }}</span>
                                    <span class="value">-${{ number_format($discount, 2) }}</span>
                                </div>
                            @endif
                            <div class="summary-row total">
                                <span class="label">{{ __('checkout.total') }}</span>
                                <span class="value">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details (Right Column) -->
                    <div class="checkout-card order-summary">
                        <h2 class="card-title">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ __('checkout.shipping_info') }}
                        </h2>
                        
                        @if($savedAddresses->count() > 0)
                            <!-- Saved Addresses Selection -->
                            <div class="saved-addresses-section">
                                <label class="form-label">{{ __('checkout.select_saved_address') }}</label>
                                <div class="saved-addresses-list">
                                    @foreach($savedAddresses as $address)
                                        <label class="saved-address-option {{ $address->is_default ? 'selected' : '' }}">
                                            <input type="radio" name="saved_address" value="{{ $address->id }}" 
                                                   data-name="{{ $address->full_name }}"
                                                   data-phone="{{ $address->phone }}"
                                                   data-address="{{ $address->address_line_1 }}{{ $address->address_line_2 ? ', ' . $address->address_line_2 : '' }}, {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}, {{ $address->country }}"
                                                   {{ $address->is_default ? 'checked' : '' }}>
                                            <div class="saved-address-content">
                                                <div class="saved-address-label">
                                                    {{ $address->label }}
                                                    @if($address->is_default)
                                                        <span class="default-badge">{{ __('checkout.default') }}</span>
                                                    @endif
                                                </div>
                                                <div class="saved-address-name">{{ $address->full_name }}</div>
                                                <div class="saved-address-details">
                                                    {{ $address->address_line_1 }}{{ $address->address_line_2 ? ', ' . $address->address_line_2 : '' }}<br>
                                                    {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                                                </div>
                                            </div>
                                            <div class="saved-address-check">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </label>
                                    @endforeach
                                    <label class="saved-address-option new-address-option">
                                        <input type="radio" name="saved_address" value="new">
                                        <div class="saved-address-content">
                                            <div class="saved-address-label">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                {{ __('checkout.use_new_address') }}
                                            </div>
                                        </div>
                                        <div class="saved-address-check">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- New Address Form (hidden by default when saved addresses exist) -->
                            <div id="newAddressForm" class="new-address-form" style="display: none;">
                        @endif
                        
                        <div class="form-group">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div>
                                    <label class="form-label">{{ __('checkout.full_name') }}</label>
                                    <input type="text" 
                                           id="customerName"
                                           placeholder="{{ __('checkout.full_name_placeholder') }}"
                                           {{ $savedAddresses->count() > 0 ? '' : 'required' }}
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="form-label">{{ __('checkout.email') }}</label>
                                    <input type="email" 
                                           id="customerEmail"
                                           placeholder="{{ __('checkout.email_placeholder') }}"
                                           required 
                                           class="form-input"
                                           value="{{ auth()->user()->email ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('checkout.shipping_address') }}</label>
                            <textarea id="shippingAddress"
                                      placeholder="{{ __('checkout.address_placeholder') }}"
                                      {{ $savedAddresses->count() > 0 ? '' : 'required' }}
                                      rows="3" 
                                      class="form-input"></textarea>
                        </div>
                        
                        @if($savedAddresses->count() > 0)
                            </div>
                        @endif

                        <div class="form-group">
                            <label class="form-label">{{ __('checkout.notes') }}</label>
                            <textarea id="notes"
                                      placeholder="{{ __('checkout.notes_placeholder') }}"
                                      rows="2" 
                                      class="form-input"></textarea>
                        </div>

                        <button type="button" 
                                onclick="openPaymentModal()"
                                class="btn-primary">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            {{ __('checkout.place_order') }} - ${{ number_format($total, 2) }}
                        </button>
                    </div>
                </div>
            </form>

            <!-- Payment Modal -->
            <div id="paymentModal" class="hidden">
                <!-- Backdrop -->
                <div class="modal-overlay" onclick="closePaymentModal()"></div>

                <!-- Modal Content -->
                <div class="modal-overlay" style="background: transparent; pointer-events: none;">
                    <div class="payment-modal" style="pointer-events: auto;">
                        <!-- Header -->
                        <div class="modal-header">
                            <h3 class="modal-title">{{ __('checkout.payment_info') }}</h3>
                            <button onclick="closePaymentModal()" class="modal-close" type="button">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="modal-body">
                            @if($savedPaymentMethods->count() > 0)
                                <!-- Saved Payment Methods -->
                                <div class="saved-payments-section">
                                    <label class="form-label">{{ __('checkout.select_saved_card') }}</label>
                                    <div class="saved-payments-list">
                                        @foreach($savedPaymentMethods as $method)
                                            <label class="saved-payment-option {{ $method->is_default ? 'selected' : '' }}">
                                                <input type="radio" name="saved_payment" value="{{ $method->id }}" 
                                                       data-last-four="{{ $method->last_four }}"
                                                       data-brand="{{ $method->brand }}"
                                                       {{ $method->is_default ? 'checked' : '' }}>
                                                <div class="saved-payment-icon">
                                                    @if($method->brand === 'visa')
                                                        <svg viewBox="0 0 50 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <rect width="50" height="35" rx="4" fill="#1A1F71"/>
                                                            <path d="M21.5 23H18.5L20.5 12H23.5L21.5 23ZM16.5 12L13.7 19.7L13.4 18.4L12.5 13.3C12.5 13.3 12.4 12 10.8 12H6.1L6 12.2C6 12.2 7.8 12.6 9.9 13.9L12.5 23H15.6L19.7 12H16.5ZM37 23H39.5L37.4 12H35C33.7 12 33.4 13 33.4 13L29 23H32.1L32.7 21.3H36.5L37 23ZM33.5 19L35.2 14.4L36.2 19H33.5ZM30.6 15.3L31 13.1C31 13.1 29.2 12.4 27.3 12.4C25.2 12.4 20.6 13.3 20.6 17.4C20.6 21.3 25.9 21.3 25.9 23.3C25.9 25.3 21.2 24.8 19.5 23.5L19.1 25.8C19.1 25.8 20.9 26.6 23.5 26.6C26.1 26.6 30.5 25.2 30.5 21.5C30.5 17.6 25.2 17.3 25.2 15.6C25.2 13.9 28.9 14.1 30.6 15.3Z" fill="white"/>
                                                        </svg>
                                                    @elseif($method->brand === 'mastercard')
                                                        <svg viewBox="0 0 50 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <rect width="50" height="35" rx="4" fill="#000"/>
                                                            <circle cx="19" cy="17.5" r="8" fill="#EB001B"/>
                                                            <circle cx="31" cy="17.5" r="8" fill="#F79E1B"/>
                                                            <path d="M25 11.5C26.9 13 28.1 15.1 28.1 17.5C28.1 19.9 26.9 22 25 23.5C23.1 22 21.9 19.9 21.9 17.5C21.9 15.1 23.1 13 25 11.5Z" fill="#FF5F00"/>
                                                        </svg>
                                                    @else
                                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="saved-payment-content">
                                                    <div class="saved-payment-number">•••• {{ $method->last_four }}</div>
                                                    <div class="saved-payment-meta">{{ $method->holder_name }} • {{ __('checkout.expires') }} {{ $method->expiry_month }}/{{ $method->expiry_year }}</div>
                                                    @if($method->is_default)
                                                        <span class="default-badge small">{{ __('checkout.default') }}</span>
                                                    @endif
                                                </div>
                                                <div class="saved-payment-check">
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </div>
                                            </label>
                                        @endforeach
                                        <label class="saved-payment-option new-card-option">
                                            <input type="radio" name="saved_payment" value="new">
                                            <div class="saved-payment-icon">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </div>
                                            <div class="saved-payment-content">
                                                <div class="saved-payment-number">{{ __('checkout.use_new_card') }}</div>
                                            </div>
                                            <div class="saved-payment-check">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- New Card Form (hidden by default) -->
                                <div id="newCardForm" class="new-card-form" style="display: none;">
                            @endif
                            
                            <!-- Payment Method Selection -->
                            <div class="payment-options" style="margin-bottom: 24px;">
                                <label class="payment-option active">
                                    <input type="radio" name="paymentMethod" value="card" checked>
                                    <div class="payment-icon">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">{{ __('checkout.credit_card') }}</div>
                                        <div class="payment-desc">Visa, Mastercard, Мир</div>
                                    </div>
                                </label>
                            </div>

                            <!-- Demo Warning -->
                            <div class="demo-warning">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p><strong>{{ __('checkout.demo_mode') }}:</strong> {{ __('checkout.demo_card_hint') }}</p>
                            </div>

                            <!-- Card Form -->
                            <div class="form-group">
                                <label class="form-label">{{ __('checkout.card_number') }}</label>
                                <input type="text" 
                                       id="cardNumber"
                                       placeholder="{{ __('checkout.card_number_placeholder') }}"
                                       maxlength="19"
                                       class="form-input">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label">{{ __('checkout.expiry_date') }}</label>
                                    <input type="text" 
                                           id="cardExpiry"
                                           placeholder="{{ __('checkout.expiry_placeholder') }}"
                                           maxlength="5"
                                           class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('checkout.cvv') }}</label>
                                    <input type="text" 
                                           id="cardCvv"
                                           placeholder="{{ __('checkout.cvv_placeholder') }}"
                                           maxlength="4"
                                           class="form-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">{{ __('checkout.cardholder_name') }}</label>
                                <input type="text" 
                                       id="cardName"
                                       placeholder="{{ __('checkout.cardholder_placeholder') }}"
                                       class="form-input">
                            </div>
                            
                            @if($savedPaymentMethods->count() > 0)
                                </div>
                            @endif
                        </div>

                        <!-- Footer -->
                        <div class="modal-footer">
                            <button onclick="closePaymentModal()" type="button" class="btn-secondary">
                                {{ __('checkout.cancel') }}
                            </button>
                            <button onclick="submitOrder()" type="button" id="payButton" class="btn-primary" style="width: auto;">
                                <span id="payButtonText">{{ __('checkout.pay') }} ${{ number_format($total, 2) }}</span>
                                <span id="processingText" class="hidden">{{ __('checkout.processing') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    window.checkoutTranslations = {
        fill_required: @json(__('checkout.fill_required')),
        fill_card_details: @json(__('checkout.fill_card_details')),
        order_success: @json(__('checkout.order_success')),
        order_error: @json(__('checkout.order_error'))
    };
</script>

@endsection
