<!-- Login History Panel -->
<div class="settings-panel" id="panel-login-history">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.login_history') }}</h3>
                    <p class="settings-section-description">{{ __('settings.login_history_description') }}</p>
                </div>
            </div>
            
            <div class="login-history-list">
                @forelse($loginHistories as $history)
                    <div class="login-history-item {{ $history->ip_address === request()->ip() ? 'current' : '' }}">
                        <div class="login-history-icon">
                            {{ $history->device_icon }}
                        </div>
                        <div class="login-history-info">
                            <div class="login-history-device">
                                {{ $history->browser }} {{ __('settings.on') }} {{ $history->platform }}
                                @if($history->ip_address === request()->ip())
                                    <span class="current-badge">{{ __('settings.current_session') }}</span>
                                @endif
                            </div>
                            <div class="login-history-meta">
                                <span>{{ $history->ip_address }}</span>
                                <span class="separator">•</span>
                                <span>{{ $history->time_ago }}</span>
                            </div>
                        </div>
                        <div class="login-history-status {{ $history->is_successful ? 'success' : 'failed' }}">
                            @if($history->is_successful)
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @else
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="settings-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p>{{ __('settings.no_login_history') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
