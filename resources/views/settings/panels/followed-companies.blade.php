<!-- Followed Companies Panel -->
<div class="settings-panel" id="panel-followed-companies">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.followed_companies') }}</h3>
                    <p class="settings-section-description">{{ __('settings.followed_companies_description') }}</p>
                </div>
            </div>
            
            <div class="followed-companies-list" id="followed-companies-list">
                @forelse($followedCompanies as $company)
                    <div class="followed-company-card" data-id="{{ $company->id }}">
                        <div class="company-avatar">
                            @if($company->logo)
                                <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}">
                            @else
                                <span>{{ substr($company->name, 0, 1) }}</span>
                            @endif
                        </div>
                        
                        <div class="company-info">
                            <div class="company-name">{{ $company->name }}</div>
                            <div class="company-meta">
                                @if($company->products_count)
                                    <span>{{ $company->products_count }} {{ __('settings.products') }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="company-actions">
                            <a href="{{ route('companies.show', $company->slug) }}" class="company-action" title="{{ __('settings.view_profile') }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <button type="button" class="company-action unfollow-company" 
                                    data-id="{{ $company->id }}"
                                    data-name="{{ $company->name }}"
                                    title="{{ __('settings.unfollow') }}">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="settings-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p>{{ __('settings.no_followed_companies') }}</p>
                        <a href="{{ route('companies.index') }}" class="settings-btn">
                            {{ __('settings.browse_companies') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
