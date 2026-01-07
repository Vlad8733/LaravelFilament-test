<!-- Orders Panel -->
<div class="settings-panel" id="panel-orders">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.my_orders') }}</h3>
                    <p class="settings-section-description">{{ __('settings.my_orders_description') }}</p>
                </div>
            </div>
            
            <div class="orders-list" id="orders-list">
                @forelse($orders as $order)
                    <div class="order-card" data-id="{{ $order->id }}">
                        <div class="order-header">
                            <div class="order-number">
                                <span class="order-number-label">{{ __('settings.order_number') }}:</span>
                                <span class="order-number-value">{{ $order->order_number }}</span>
                                <button type="button" class="copy-order-number" data-number="{{ $order->order_number }}" title="{{ __('settings.copy') }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="order-status order-status-{{ strtolower(str_replace(' ', '-', $order->order_status ?? 'pending')) }}">
                                {{ $order->order_status ?? 'Pending' }}
                            </div>
                        </div>
                        
                        <div class="order-details">
                            <div class="order-detail">
                                <span class="order-detail-label">{{ __('settings.order_date') }}:</span>
                                <span class="order-detail-value">{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="order-detail">
                                <span class="order-detail-label">{{ __('settings.order_total') }}:</span>
                                <span class="order-detail-value order-total">${{ number_format($order->total, 2) }}</span>
                            </div>
                        </div>
                        
                        @if($order->items->count() > 0)
                        <div class="order-items-list">
                            @foreach($order->items as $item)
                                <div class="order-item-row">
                                    <span class="order-item-name">{{ $item->product_name }}</span>
                                    <span class="order-item-qty">×{{ $item->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <div class="order-actions">
                            <a href="{{ route('orders.tracking.show', $order->order_number) }}" class="order-action-btn">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                {{ __('settings.view_order') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="settings-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p>{{ __('settings.no_orders') }}</p>
                        <a href="{{ route('products.index') }}" class="settings-btn">
                            {{ __('settings.start_shopping') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
