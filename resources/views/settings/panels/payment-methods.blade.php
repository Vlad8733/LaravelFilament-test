<!-- Payment Methods Panel -->
<div class="settings-panel" id="panel-payment-methods">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.payment_methods') }}</h3>
                    <p class="settings-section-description">{{ __('settings.payment_methods_description') }}</p>
                </div>
            </div>
            
            <div class="settings-info-box">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>{{ __('settings.payment_security_notice') }}</p>
            </div>
            
            <div class="payment-methods-list" id="payment-methods-list">
                @forelse($paymentMethods as $method)
                    <div class="payment-card {{ $method->is_default ? 'default' : '' }} {{ $method->is_expired ? 'expired' : '' }}" data-id="{{ $method->id }}">
                        <div class="payment-card-icon">
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
                        
                        <div class="payment-card-info">
                            <div class="payment-card-number">
                                {{ $method->masked_number }}
                                @if($method->is_default)
                                    <span class="default-badge">{{ __('settings.default') }}</span>
                                @endif
                                @if($method->is_expired)
                                    <span class="expired-badge">{{ __('settings.expired') }}</span>
                                @endif
                            </div>
                            <div class="payment-card-meta">
                                <span>{{ $method->holder_name }}</span>
                                <span class="separator">•</span>
                                <span>{{ __('settings.expires') }} {{ $method->expiry_month }}/{{ $method->expiry_year }}</span>
                            </div>
                        </div>
                        
                        <div class="payment-card-actions">
                            @unless($method->is_default)
                                <button type="button" class="payment-action set-default-payment" 
                                        data-id="{{ $method->id }}"
                                        title="{{ __('settings.set_as_default') }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </button>
                            @endunless
                            <button type="button" class="payment-action edit-payment" 
                                    data-id="{{ $method->id }}"
                                    data-payment="{{ json_encode($method) }}"
                                    title="{{ __('settings.edit') }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button type="button" class="payment-action delete-payment" 
                                    data-id="{{ $method->id }}"
                                    title="{{ __('settings.delete') }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="settings-empty" id="no-payment-methods">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p>{{ __('settings.no_payment_methods') }}</p>
                        <button type="button" class="settings-btn" id="add-first-payment-btn">
                            {{ __('settings.add_your_first_card') }}
                        </button>
                    </div>
                @endforelse
                
                @if($paymentMethods->count() > 0)
                    <div class="settings-add-new">
                        <button type="button" class="settings-btn settings-btn-outline" id="add-payment-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('settings.add_new_payment_method') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
