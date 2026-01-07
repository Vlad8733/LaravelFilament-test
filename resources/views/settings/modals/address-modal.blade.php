<!-- Address Modal -->
<div class="settings-modal" id="address-modal">
    <div class="settings-modal-backdrop"></div>
    <div class="settings-modal-container">
        <div class="settings-modal-content">
            <div class="settings-modal-header">
                <h3 class="settings-modal-title" id="address-modal-title">{{ __('settings.add_address') }}</h3>
                <button type="button" class="settings-modal-close" id="close-address-modal">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="address-form" class="settings-modal-body">
                @csrf
                <input type="hidden" name="address_id" id="address_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.address_label') }} *</label>
                        <input type="text" name="label" id="address_label" class="form-input" 
                               placeholder="{{ __('settings.address_label_placeholder') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.full_name') }} *</label>
                        <input type="text" name="full_name" id="address_full_name" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.phone') }} *</label>
                        <input type="tel" name="phone" id="address_phone" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.address_line1') }} *</label>
                        <input type="text" name="address_line1" id="address_line1" class="form-input" 
                               placeholder="{{ __('settings.address_line1_placeholder') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.address_line2') }}</label>
                        <input type="text" name="address_line2" id="address_line2" class="form-input"
                               placeholder="{{ __('settings.address_line2_placeholder') }}">
                    </div>
                </div>
                
                <div class="form-row form-row-2col">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.city') }} *</label>
                        <input type="text" name="city" id="address_city" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.state') }}</label>
                        <input type="text" name="state" id="address_state" class="form-input">
                    </div>
                </div>
                
                <div class="form-row form-row-2col">
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.postal_code') }} *</label>
                        <input type="text" name="postal_code" id="address_postal_code" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('settings.country') }} *</label>
                        <select name="country" id="address_country" class="form-select" required>
                            <option value="">{{ __('settings.select_country') }}</option>
                            @foreach(\App\Models\UserAddress::countryOptions() as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <label class="form-checkbox">
                        <input type="checkbox" name="is_default" id="address_is_default">
                        <span class="checkmark"></span>
                        {{ __('settings.set_as_default_address') }}
                    </label>
                    <p class="form-hint" id="first-address-hint" style="display: none; margin-top: 6px;">
                        {{ __('settings.first_address_default_hint') }}
                    </p>
                    <div class="default-address-warning" id="default-address-warning" style="display: none;">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ __('settings.cannot_uncheck_default_address') }}</span>
                    </div>
                </div>
            </form>
            
            <div class="settings-modal-footer">
                <button type="button" class="settings-btn settings-btn-outline" id="cancel-address-modal">
                    {{ __('settings.cancel') }}
                </button>
                <button type="submit" form="address-form" class="settings-btn settings-btn-primary" id="save-address-btn">
                    <svg class="btn-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0110 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path>
                    </svg>
                    <span class="btn-text" id="address-btn-text">{{ __('settings.save_address') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
