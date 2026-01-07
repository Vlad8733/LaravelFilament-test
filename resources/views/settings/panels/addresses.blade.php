<!-- Addresses Panel -->
<div class="settings-panel" id="panel-addresses">
    <div class="settings-card">
        <div class="settings-section">
            <div class="settings-section-header">
                <div class="settings-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="settings-section-info">
                    <h3 class="settings-section-title">{{ __('settings.saved_addresses') }}</h3>
                    <p class="settings-section-description">{{ __('settings.saved_addresses_description') }}</p>
                </div>
            </div>
            
            <div class="addresses-list" id="addresses-list">
                @forelse($addresses as $address)
                    <div class="address-card {{ $address->is_default ? 'default' : '' }}" data-id="{{ $address->id }}">
                        <div class="address-card-header">
                            <div class="address-label">
                                {{ $address->label }}
                                @if($address->is_default)
                                    <span class="default-badge">{{ __('settings.default') }}</span>
                                @endif
                            </div>
                            <div class="address-actions">
                                @unless($address->is_default)
                                    <button type="button" class="address-action set-default-address" 
                                            data-id="{{ $address->id }}"
                                            title="{{ __('settings.set_as_default') }}">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                @endunless
                                <button type="button" class="address-action edit-address" 
                                        data-id="{{ $address->id }}"
                                        data-address="{{ json_encode($address) }}"
                                        title="{{ __('settings.edit') }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" class="address-action delete-address" 
                                        data-id="{{ $address->id }}"
                                        title="{{ __('settings.delete') }}">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="address-card-body">
                            <p class="address-name">{{ $address->full_name }}</p>
                            <p class="address-line">{{ $address->address_line1 }}</p>
                            @if($address->address_line2)
                                <p class="address-line">{{ $address->address_line2 }}</p>
                            @endif
                            <p class="address-line">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                            <p class="address-line">{{ $address->country }}</p>
                            <p class="address-phone">{{ $address->phone }}</p>
                        </div>
                    </div>
                @empty
                    <div class="settings-empty" id="no-addresses">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p>{{ __('settings.no_addresses') }}</p>
                        <button type="button" class="settings-btn" id="add-first-address-btn">
                            {{ __('settings.add_your_first_address') }}
                        </button>
                    </div>
                @endforelse
                
                @if($addresses->count() > 0)
                    <div class="settings-add-new">
                        <button type="button" class="settings-btn settings-btn-outline" id="add-address-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('settings.add_new_address') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
