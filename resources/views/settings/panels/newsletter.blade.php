<!-- Newsletter Panel -->
<div class="settings-panel" id="panel-newsletter">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.newsletter') }}</h3>
                    <p class="settings-section-description">{{ __('settings.newsletter_description') }}</p>
                </div>
            </div>
            
            <div class="newsletter-card">
                <div class="newsletter-content">
                    <div class="newsletter-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <div class="newsletter-info">
                        <h4>{{ __('settings.newsletter_title') }}</h4>
                        <p>{{ __('settings.newsletter_benefits') }}</p>
                    </div>
                </div>
                
                <div class="newsletter-toggle">
                    <label class="toggle-switch toggle-switch-lg">
                        <input type="checkbox" 
                               id="newsletter-toggle" 
                               {{ auth()->user()->newsletter_subscribed ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-status">
                        {{ auth()->user()->newsletter_subscribed ? __('settings.subscribed') : __('settings.not_subscribed') }}
                    </span>
                </div>
                
                @if(auth()->user()->newsletter_subscribed && auth()->user()->newsletter_subscribed_at)
                    <div class="newsletter-subscribed-info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('settings.subscribed_since') }} {{ auth()->user()->newsletter_subscribed_at->format('d M Y') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
