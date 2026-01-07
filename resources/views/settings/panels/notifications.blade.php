<!-- Notifications Panel -->
<div class="settings-panel" id="panel-notifications">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.notifications') }}</h3>
                    <p class="settings-section-description">{{ __('settings.notifications_description') }}</p>
                </div>
            </div>
            
            <div class="settings-toggle-group">
                <div class="settings-toggle-item">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-label">{{ __('settings.email_notifications') }}</p>
                        <p class="settings-toggle-hint">{{ __('settings.email_notifications_hint') }}</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" data-setting="email_notifications" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="settings-toggle-item">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-label">{{ __('settings.order_updates') }}</p>
                        <p class="settings-toggle-hint">{{ __('settings.order_updates_hint') }}</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" data-setting="order_updates" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                
                <div class="settings-toggle-item">
                    <div class="settings-toggle-info">
                        <p class="settings-toggle-label">{{ __('settings.promotions') }}</p>
                        <p class="settings-toggle-hint">{{ __('settings.promotions_hint') }}</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" data-setting="promotions">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
