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
                    <option value="lv" {{ auth()->user()->locale === 'lv' ? 'selected' : '' }}>
                        🇱🇻 {{ __('settings.latvian') }}
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
