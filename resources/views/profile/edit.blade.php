@extends('layouts.app')

@section('title', __('profile.title'))

@push('styles')
    @vite('resources/css/profile/profileedit.css')
@endpush

@push('scripts')
    @vite('resources/js/profile/profileedit.js')
    @vite('resources/js/account/accounts.js')
    <script>
        window.__routes = window.__routes || {};
        window.__routes.profileAccountsSwitch = "{{ route('profile.accounts.switch') }}";
    </script>
@endpush

@section('content')
<main class="profile-page">
  <div class="profile-container">
    {{-- Status Message --}}
    @if(session('status'))
      <div class="status-toast">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        {{ session('status') }}
      </div>
    @endif

    {{-- Page Header --}}
    <header class="profile-header">
      <div class="profile-header-content">
        <div class="profile-avatar-section">
          <div class="avatar-wrapper" id="avatarPreview">
            @if($user->avatar)
              <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}">
            @else
              <span class="avatar-initials">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
            @endif
            <label class="avatar-upload-btn" for="avatarInput" title="{{ __('profile.change_avatar') }}">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </label>
          </div>
          <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatarForm" class="hidden-form">
            @csrf
            <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="this.form.submit()">
          </form>
        </div>
        <div class="profile-info">
          <h1 class="profile-name">
            {{ $user->name }}
            @if($user->hasRole('admin'))
              <span class="admin-badge" data-tooltip="{{ __('profile.admin') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L15.5 8.5L23 9.5L17.5 15L19 23L12 19L5 23L6.5 15L1 9.5L8.5 8.5L12 1Z"/></svg>
                Admin
              </span>
            @endif
          </h1>
          <p class="profile-email">{{ $user->email }}</p>
          <div class="profile-meta">
            <span class="meta-item">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              {{ __('profile.member_since') }} {{ $user->created_at->format('M Y') }}
            </span>
          </div>
        </div>
      </div>

      {{-- Quick Actions --}}
      <div class="profile-actions">
        @if($user->hasRole('admin'))
          <a href="/admin" target="_blank" class="action-btn action-btn--admin">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            {{ __('profile.open_admin') }}
          </a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="inline-form">
          @csrf
          <button type="submit" class="action-btn action-btn--danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          </button>
        </form>
      </div>
    </header>

    {{-- Main Content Grid --}}
    <div class="profile-grid">
      {{-- Left Column --}}
      <div class="profile-column">
        {{-- Account Settings --}}
        <section class="profile-section">
          <div class="section-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <h2>{{ __('profile.account_settings') }}</h2>
          </div>
          <form method="POST" action="{{ route('profile.update') }}" class="settings-form">
            @csrf
            <div class="form-group">
              <label class="form-label">{{ __('profile.name') }}</label>
              <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
              @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
              <label class="form-label">{{ __('profile.email') }}</label>
              <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
              @error('email') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">{{ __('profile.new_password') }}</label>
                <input type="password" name="password" class="form-input" autocomplete="new-password" placeholder="••••••••">
                @error('password') <span class="form-error">{{ $message }}</span> @enderror
              </div>
              <div class="form-group">
                <label class="form-label">{{ __('profile.confirm_password') }}</label>
                <input type="password" name="password_confirmation" class="form-input" autocomplete="new-password" placeholder="••••••••">
              </div>
            </div>
            <button type="submit" class="btn-primary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              {{ __('profile.save_account') }}
            </button>
          </form>
        </section>

        {{-- Danger Zone --}}
        <section class="profile-section profile-section--danger">
          <div class="section-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h2>{{ __('profile.danger_zone') }}</h2>
          </div>
          <p class="section-desc">{{ __('profile.password_hint') }}</p>
          <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('{{ __('profile.delete_confirm') }}');" class="danger-form">
            @csrf
            @method('DELETE')
            <div class="form-group">
              <input type="password" name="current_password" class="form-input" placeholder="{{ __('profile.current_password') }}">
            </div>
            <button type="submit" class="btn-danger">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              {{ __('profile.delete_account') }}
            </button>
          </form>
        </section>
      </div>

      {{-- Right Column --}}
      <div class="profile-column">
        {{-- Account Switcher --}}
        @php
            $authUser = auth()->user();
            $master = $authUser->parent_user_id 
                ? \App\Models\User::with(['children' => fn($q) => $q->orderBy('created_at', 'asc')])->find($authUser->parent_user_id)
                : \App\Models\User::with(['children' => fn($q) => $q->orderBy('created_at', 'asc')])->find($authUser->id);
            $related = collect([$master])->merge($master->children ?? collect());
        @endphp

        <section class="profile-section" id="account-switcher">
          <div class="section-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <h2>{{ __('profile.account_switcher') }}</h2>
          </div>

          <div class="accounts-list">
            @foreach($related as $acc)
              <button type="button"
                class="account-card {{ $acc->id === $user->id ? 'account-card--active' : '' }}"
                onclick="switchAccount({{ $acc->id }})"
                @if($acc->id === $user->id) disabled @endif>
                <img class="account-avatar"
                  src="{{ $acc->avatar ? asset('storage/' . $acc->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($acc->email))) . '?s=56&d=identicon' }}"
                  alt="{{ $acc->name }}">
                <div class="account-info">
                  <span class="account-name">
                    {{ $acc->name }}
                    @if($acc->hasRole('admin'))
                      <svg class="admin-icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L15.5 8.5L23 9.5L17.5 15L19 23L12 19L5 23L6.5 15L1 9.5L8.5 8.5L12 1Z"/></svg>
                    @endif
                  </span>
                  <span class="account-email">{{ $acc->email }}</span>
                </div>
                @if($acc->id === $user->id)
                  <span class="account-active-badge">Active</span>
                @endif
              </button>
            @endforeach

            <a href="{{ route('profile.accounts.create-child') }}" class="account-card account-card--add">
              <div class="add-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
              </div>
              <div class="account-info">
                <span class="account-name">{{ __('profile.create_account') }}</span>
                <span class="account-email">{{ __('profile.link_to_main') }}</span>
              </div>
            </a>
          </div>

          <p class="section-hint">{{ __('profile.switcher_hint') }}</p>
        </section>
      </div>
    </div>
  </div>
</main>
@endsection
