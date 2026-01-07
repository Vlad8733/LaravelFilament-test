<!-- Payment Method Modal -->
<div class="settings-modal" id="payment-modal">
    <div class="settings-modal-backdrop"></div>
    <div class="settings-modal-container">
        <div class="settings-modal-content">
            <div class="settings-modal-header">
                <h3 class="settings-modal-title" id="payment-modal-title">{{ __('settings.add_payment_method') }}</h3>
                <button type="button" class="settings-modal-close" id="close-payment-modal">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="settings-modal-body">
                <div class="payment-info-notice" id="payment-info-notice">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <div>
                        <strong>{{ __('settings.secure_payment') }}</strong>
                        <p>{{ __('settings.secure_payment_description') }}</p>
                    </div>
                </div>
                
                <form id="payment-form">
                    @csrf
                    <input type="hidden" name="payment_id" id="payment_id">
                    
                    <div class="form-row" id="card-number-row">
                        <div class="form-group">
                            <label class="form-label">{{ __('settings.card_number') }} *</label>
                            <div class="card-input-wrapper">
                                <input type="text" name="card_number" id="card_number" class="form-input card-number-input" 
                                       placeholder="1234 5678 9012 3456" 
                                       maxlength="19"
                                       autocomplete="cc-number"
                                       required>
                                <div class="card-brand-icon" id="card-brand-icon"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">{{ __('settings.holder_name') }} *</label>
                            <input type="text" name="holder_name" id="holder_name" class="form-input" 
                                   placeholder="{{ __('settings.holder_name_placeholder') }}"
                                   autocomplete="cc-name"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row form-row-3col">
                        <div class="form-group">
                            <label class="form-label">{{ __('settings.expiry_month') }} *</label>
                            <select name="expiry_month" id="expiry_month" class="form-select" required>
                                <option value="">{{ __('settings.mm') }}</option>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('settings.expiry_year') }} *</label>
                            <select name="expiry_year" id="expiry_year" class="form-select" required>
                                <option value="">{{ __('settings.yy') }}</option>
                                @for($y = date('Y'); $y <= date('Y') + 15; $y++)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('settings.cvv') }} *</label>
                            <input type="text" name="cvv" id="cvv" class="form-input" 
                                   placeholder="123" 
                                   maxlength="4"
                                   autocomplete="cc-csc"
                                   required>
                            <span class="form-hint">{{ __('settings.cvv_hint') }}</span>
                        </div>
                    </div>
                    
                    <div class="form-row" id="payment-default-row">
                        <label class="form-checkbox">
                            <input type="checkbox" name="is_default" id="payment_is_default">
                            <span class="checkmark"></span>
                            {{ __('settings.set_as_default_payment') }}
                        </label>
                    </div>
                </form>
                
                <div class="demo-notice">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>{{ __('settings.demo_payment_notice') }}</p>
                </div>
            </div>
            
            <div class="settings-modal-footer">
                <button type="button" class="settings-btn settings-btn-outline" id="cancel-payment-modal">
                    {{ __('settings.cancel') }}
                </button>
                <button type="submit" form="payment-form" class="settings-btn settings-btn-primary" id="save-payment-btn">
                    <svg class="btn-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span class="btn-text" id="payment-btn-text">{{ __('settings.add_card') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
