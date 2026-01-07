<!-- Security Panel -->
<div class="settings-panel" id="panel-security">
    <div class="settings-card">
        <!-- Password Change -->
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.change_password') }}</h3>
                    <p class="settings-section-description">{{ __('settings.change_password_description') }}</p>
                </div>
            </div>
            
            <form id="password-form" class="settings-form">
                @csrf
                <div class="settings-form-group">
                    <label class="settings-label">{{ __('settings.current_password') }}</label>
                    <input type="password" name="current_password" class="settings-input" required>
                </div>
                
                <div class="settings-form-group">
                    <label class="settings-label">{{ __('settings.new_password') }}</label>
                    <input type="password" name="password" class="settings-input" minlength="8" required>
                </div>
                
                <div class="settings-form-group">
                    <label class="settings-label">{{ __('settings.confirm_password') }}</label>
                    <input type="password" name="password_confirmation" class="settings-input" required>
                </div>
                
                <button type="submit" class="settings-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('settings.update_password') }}
                </button>
            </form>
        </div>
        
        <div class="settings-divider"></div>
        
        <!-- Two-Factor Authentication -->
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.two_factor_auth') }}</h3>
                    <p class="settings-section-description">{{ __('settings.two_factor_description') }}</p>
                </div>
            </div>
            
            <div class="settings-2fa-status">
                @if(auth()->user()->hasTwoFactorEnabled())
                    <div class="status-badge status-enabled">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('settings.2fa_enabled') }}
                    </div>
                    <a href="{{ route('two-factor.index') }}" class="settings-btn settings-btn-danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        {{ __('settings.disable_2fa') }}
                    </a>
                @else
                    <div class="status-badge status-disabled">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        {{ __('settings.2fa_disabled') }}
                    </div>
                    <a href="{{ route('two-factor.index') }}" class="settings-btn settings-btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('settings.enable_2fa') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
