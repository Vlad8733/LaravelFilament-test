@extends('layouts.app')

@section('title', __('settings.title'))

@push('styles')
    @vite('resources/css/settings/settings.css')
@endpush

@section('content')
<div class="settings-page">
    <div class="settings-container">
        <!-- Back Link -->
        <a href="{{ route('profile.edit') }}" class="settings-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ __('settings.back_to_profile') }}
        </a>

        <!-- Header -->
        <div class="settings-header">
            <h1 class="settings-title">{{ __('settings.title') }}</h1>
            <p class="settings-subtitle">{{ __('settings.subtitle') }}</p>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="settings-alert">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Layout Grid -->
        <div class="settings-layout">
            <!-- Sidebar Navigation -->
            <aside class="settings-sidebar">
                <nav class="settings-nav">
                    <button type="button" class="settings-nav-item active" data-panel="language">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        {{ __('settings.nav_language') }}
                    </button>
                    
                    <button type="button" class="settings-nav-item" data-panel="notifications">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        {{ __('settings.nav_notifications') }}
                    </button>
                    
                    <button type="button" class="settings-nav-item" data-panel="privacy">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        {{ __('settings.nav_privacy') }}
                    </button>
                    
                    <button type="button" class="settings-nav-item" data-panel="appearance">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        {{ __('settings.nav_appearance') }}
                    </button>
                    
                    <!-- Analytics Link -->
                    <div class="settings-nav-divider"></div>
                    
                    <a href="{{ route('analytics.index') }}" class="settings-nav-item settings-nav-link">
                        <svg class="settings-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ __('settings.nav_analytics') }}
                        <svg class="settings-nav-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </nav>
            </aside>

            <!-- Content Area -->
            <div class="settings-content">
                <!-- Language Panel -->
                <div class="settings-panel active" id="panel-language">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="settings-section-header">
                                <div class="settings-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                                    </svg>
                                </div>
                                <div class="settings-section-info">
                                    <h3 class="settings-section-title">{{ __('settings.language') }}</h3>
                                    <p class="settings-section-description">{{ __('settings.language_description') }}</p>
                                </div>
                            </div>
                            
                            <form action="{{ route('settings.locale') }}" method="POST">
                                @csrf
                                <select name="locale" class="settings-select">
                                    <option value="en" {{ auth()->user()->locale === 'en' ? 'selected' : '' }}>
                                        🇺🇸 {{ __('settings.english') }}
                                    </option>
                                    <option value="ru" {{ auth()->user()->locale === 'ru' ? 'selected' : '' }}>
                                        🇷🇺 {{ __('settings.russian') }}
                                    </option>
                                </select>
                                
                                <button type="submit" class="settings-btn">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ __('settings.save') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

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
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="settings-toggle-item">
                                    <div class="settings-toggle-info">
                                        <p class="settings-toggle-label">{{ __('settings.order_updates') }}</p>
                                        <p class="settings-toggle-hint">{{ __('settings.order_updates_hint') }}</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="settings-toggle-item">
                                    <div class="settings-toggle-info">
                                        <p class="settings-toggle-label">{{ __('settings.promotions') }}</p>
                                        <p class="settings-toggle-hint">{{ __('settings.promotions_hint') }}</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="button" class="settings-btn">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('settings.save_changes') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Privacy Panel -->
                <div class="settings-panel" id="panel-privacy">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="settings-section-header">
                                <div class="settings-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div class="settings-section-info">
                                    <h3 class="settings-section-title">{{ __('settings.privacy') }}</h3>
                                    <p class="settings-section-description">{{ __('settings.privacy_description') }}</p>
                                </div>
                            </div>
                            
                            <div class="settings-toggle-group">
                                <div class="settings-toggle-item">
                                    <div class="settings-toggle-info">
                                        <p class="settings-toggle-label">{{ __('settings.profile_visibility') }}</p>
                                        <p class="settings-toggle-hint">{{ __('settings.profile_visibility_hint') }}</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                
                                <div class="settings-toggle-item">
                                    <div class="settings-toggle-info">
                                        <p class="settings-toggle-label">{{ __('settings.show_activity') }}</p>
                                        <p class="settings-toggle-hint">{{ __('settings.show_activity_hint') }}</p>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="button" class="settings-btn">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('settings.save_changes') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Appearance Panel -->
                <div class="settings-panel" id="panel-appearance">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="settings-section-header">
                                <div class="settings-section-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                </div>
                                <div class="settings-section-info">
                                    <h3 class="settings-section-title">{{ __('settings.appearance') }}</h3>
                                    <p class="settings-section-description">{{ __('settings.appearance_description') }}</p>
                                </div>
                            </div>
                            
                            <div class="theme-cards">
                                <label class="theme-card">
                                    <input type="radio" name="theme" value="dark" checked>
                                    <div class="theme-card-content">
                                        <div class="theme-icon dark">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                        </div>
                                        <span class="theme-label">{{ __('settings.theme_dark') }}</span>
                                    </div>
                                </label>
                                
                                <label class="theme-card">
                                    <input type="radio" name="theme" value="light">
                                    <div class="theme-card-content">
                                        <div class="theme-icon light">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                        </div>
                                        <span class="theme-label">{{ __('settings.theme_light') }}</span>
                                    </div>
                                </label>
                                
                                <label class="theme-card">
                                    <input type="radio" name="theme" value="auto">
                                    <div class="theme-card-content">
                                        <div class="theme-icon auto">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #71717a;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="theme-label">{{ __('settings.theme_auto') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ==========================================
        // SETTINGS STORAGE KEYS
        // ==========================================
        const STORAGE_KEYS = {
            activePanel: 'settings_active_panel',
            theme: 'settings_theme',
            emailNotifications: 'settings_email_notifications',
            orderUpdates: 'settings_order_updates',
            promotions: 'settings_promotions',
            profileVisibility: 'settings_profile_visibility',
            showActivity: 'settings_show_activity'
        };

        // ==========================================
        // TOAST NOTIFICATIONS (same style as products page)
        // ==========================================
        function showToast(message, type = 'success') {
            // Create toast container if not exists
            let container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type}`;
            toast.innerHTML = `
                <div class="toast-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="toast-content">
                    <div class="toast-product-name">{{ __('settings.title') }}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <div class="toast-progress"></div>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            requestAnimationFrame(() => {
                toast.classList.add('toast-enter');
            });
            
            // Close button
            toast.querySelector('.toast-close').addEventListener('click', () => removeToast(toast));
            
            // Auto remove after 4s
            setTimeout(() => removeToast(toast), 4000);
        }
        
        function removeToast(toast) {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-leave');
            setTimeout(() => toast.remove(), 500);
        }

        // ==========================================
        // THEME MANAGEMENT
        // ==========================================
        function applyTheme(theme) {
            document.body.classList.remove('theme-dark', 'theme-light', 'theme-auto');
            document.documentElement.classList.remove('theme-dark', 'theme-light', 'theme-auto');
            
            if (theme === 'auto') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const effectiveTheme = prefersDark ? 'dark' : 'light';
                document.body.classList.add('theme-' + effectiveTheme);
                document.documentElement.classList.add('theme-' + effectiveTheme);
            } else {
                document.body.classList.add('theme-' + theme);
                document.documentElement.classList.add('theme-' + theme);
            }
        }

        function initTheme() {
            const savedTheme = localStorage.getItem(STORAGE_KEYS.theme) || 'dark';
            applyTheme(savedTheme);
            
            // Set correct radio button
            const themeRadio = document.querySelector(`input[name="theme"][value="${savedTheme}"]`);
            if (themeRadio) {
                themeRadio.checked = true;
            }
        }

        // Listen for system theme changes when auto is selected
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function() {
            const currentTheme = localStorage.getItem(STORAGE_KEYS.theme);
            if (currentTheme === 'auto') {
                applyTheme('auto');
            }
        });

        // Theme radio buttons
        const themeRadios = document.querySelectorAll('input[name="theme"]');
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const theme = this.value;
                localStorage.setItem(STORAGE_KEYS.theme, theme);
                applyTheme(theme);
                showToast('{{ __('settings.changes_saved') }}', 'success');
            });
        });

        // ==========================================
        // NAVIGATION PANEL SWITCHING
        // ==========================================
        const navItems = document.querySelectorAll('.settings-nav-item');
        const panels = document.querySelectorAll('.settings-panel');
        
        function switchToPanel(panelId) {
            navItems.forEach(nav => {
                nav.classList.remove('active');
                if (nav.dataset.panel === panelId) {
                    nav.classList.add('active');
                }
            });
            
            panels.forEach(panel => {
                panel.classList.remove('active');
                if (panel.id === 'panel-' + panelId) {
                    panel.classList.add('active');
                }
            });
        }

        function initActivePanel() {
            const savedPanel = localStorage.getItem(STORAGE_KEYS.activePanel) || 'language';
            switchToPanel(savedPanel);
        }

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                const panelId = this.dataset.panel;
                localStorage.setItem(STORAGE_KEYS.activePanel, panelId);
                switchToPanel(panelId);
            });
        });

        // ==========================================
        // TOGGLE SWITCHES (Notifications & Privacy)
        // ==========================================
        const toggleMappings = [
            { selector: '#panel-notifications .settings-toggle-item:nth-child(1) input', key: STORAGE_KEYS.emailNotifications, default: true },
            { selector: '#panel-notifications .settings-toggle-item:nth-child(2) input', key: STORAGE_KEYS.orderUpdates, default: true },
            { selector: '#panel-notifications .settings-toggle-item:nth-child(3) input', key: STORAGE_KEYS.promotions, default: false },
            { selector: '#panel-privacy .settings-toggle-item:nth-child(1) input', key: STORAGE_KEYS.profileVisibility, default: true },
            { selector: '#panel-privacy .settings-toggle-item:nth-child(2) input', key: STORAGE_KEYS.showActivity, default: false }
        ];

        function initToggles() {
            toggleMappings.forEach(mapping => {
                const toggle = document.querySelector(mapping.selector);
                if (toggle) {
                    const savedValue = localStorage.getItem(mapping.key);
                    toggle.checked = savedValue !== null ? savedValue === 'true' : mapping.default;
                    
                    toggle.addEventListener('change', function() {
                        localStorage.setItem(mapping.key, this.checked);
                    });
                }
            });
        }

        // ==========================================
        // SAVE BUTTONS
        // ==========================================
        const saveButtons = document.querySelectorAll('.settings-btn:not([type="submit"])');
        saveButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                showToast('{{ __('settings.changes_saved') }}', 'success');
            });
        });

        // ==========================================
        // INITIALIZATION
        // ==========================================
        initTheme();
        initActivePanel();
        initToggles();
    });

    // Apply theme immediately to prevent flash
    (function() {
        const savedTheme = localStorage.getItem('settings_theme') || 'dark';
        document.documentElement.classList.remove('theme-light', 'theme-dark');
        if (savedTheme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.add('theme-' + (prefersDark ? 'dark' : 'light'));
        } else {
            document.documentElement.classList.add('theme-' + savedTheme);
        }
    })();
</script>
@endpush