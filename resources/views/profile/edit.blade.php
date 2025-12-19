@extends('layouts.app')

@push('styles')
    @vite('resources/css/profile/profileedit.css')
@endpush

@push('scripts')
    @vite('resources/js/profile/profileedit.js')
@endpush

@push('scripts')
    @vite('resources/js/account/accounts.js')
    <script>
        window.__routes = window.__routes || {};
        window.__routes.profileAccountsSwitch = "{{ route('profile.accounts.switch') }}";
    </script>
@endpush

@section('content')
<main class="py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if(session('status'))
      <div class="mb-4 text-green-600">{{ session('status') }}</div>
    @endif

    <div class="profile-card">
      <div class="flex gap-4 flex-wrap">
        <!-- Avatar panel -->
        <aside class="w-[280px]">
          <div class="text-center mb-3">
            <div class="avatar" id="avatarPreview">
              @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="avatar">
              @else
                <span>{{ strtoupper(substr($user->name,0,1) ?? 'U') }}</span>
              @endif
            </div>
          </div>

          <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
            @csrf
            <div>
              <label class="field-label">Change avatar</label>
              <input type="file" name="avatar" id="avatarInput" accept="image/*" class="input-field">
              @error('avatar') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3">
              <button class="btn-primary" type="submit">Upload avatar</button>
            </div>
          </form>

          <!-- Admin Panel Button -->
          @if($user->hasRole('admin'))
          <div class="mt-4 settings-card">
            <div class="mb-2 font-bold">Admin Panel</div>
            <a href="/admin" class="btn-admin">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
              Open Admin Panel
            </a>
          </div>
          @endif

          <div class="mt-4 settings-card">
            <div class="mb-2 font-bold">Theme</div>
            <div class="flex gap-2 items-center">
              <button id="themeToggle" class="btn-primary" type="button">Toggle theme</button>
              <small class="text-gray-500">Stored locally</small>
            </div>
          </div>

          <!-- Logout Button -->
          <div class="mt-4 settings-card">
            <div class="mb-2 font-bold">Session</div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="btn-logout">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                  <polyline points="16 17 21 12 16 7"></polyline>
                  <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Log out
              </button>
            </form>
          </div>
        </aside>

        <!-- Account panel -->
        <section class="flex-1">
          <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <div>
              <label class="field-label">
                Name
                @if($user->hasRole('admin'))
                  <span class="admin-crown" data-tooltip="Admin">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 1L15.5 8.5L23 9.5L17.5 15L19 23L12 19L5 23L6.5 15L1 9.5L8.5 8.5L12 1Z" stroke="currentColor" stroke-width="1.5"/>
                      <circle cx="12" cy="10" r="1.5"/>
                      <circle cx="7" cy="12" r="1.5"/>
                      <circle cx="17" cy="12" r="1.5"/>
                    </svg>
                  </span>
                @endif
              </label>
              <input type="text" name="name" value="{{ old('name',$user->name) }}" class="input-field" required>
              @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3">
              <label class="field-label">Email</label>
              <input type="email" name="email" value="{{ old('email',$user->email) }}" class="input-field" required>
              @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3">
              <label class="field-label">New password (leave blank to keep)</label>
              <input type="password" name="password" class="input-field" autocomplete="new-password">
              @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="mt-3">
              <label class="field-label">Confirm password</label>
              <input type="password" name="password_confirmation" class="input-field" autocomplete="new-password">
            </div>

            <div class="mt-4">
              <button class="btn-primary" type="submit">Save account</button>
            </div>
          </form>

          <hr class="my-6 border-t" />

          <!-- Account Switcher -->
          @php
              $authUser = auth()->user();
              if ($authUser->parent_user_id) {
                  $master = \App\Models\User::with(['children' => function($q){ $q->orderBy('created_at','asc'); }])->find($authUser->parent_user_id);
              } else {
                  $master = \App\Models\User::with(['children' => function($q){ $q->orderBy('created_at','asc'); }])->find($authUser->id);
              }
              $related = collect([$master])->merge($master->children ?? collect());
          @endphp

          <div class="mt-6 card" id="account-switcher">
              <h3 class="font-semibold mb-3">Account Switcher</h3>

              <div class="account-switcher-vertical">
                  @foreach($related as $acc)
                      <button
                          type="button"
                          class="account-card {{ $acc->id === $user->id ? 'account-card--active' : '' }}"
                          onclick="switchAccount({{ $acc->id }})"
                          aria-pressed="{{ $acc->id === $user->id ? 'true' : 'false' }}"
                          aria-current="{{ $acc->id === $user->id ? 'true' : 'false' }}"
                          @if($acc->id === $user->id) disabled @endif
                      >
                          <img
                              class="account-avatar"
                              src="{{ $acc->avatar ? asset('storage/' . $acc->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($acc->email))) . '?s=56&d=identicon' }}"
                              alt="avatar">

                          <div class="account-info">
                              <div class="account-name">
                                {{ $acc->name }}
                                @if($acc->hasRole('admin'))
                                  <span class="admin-crown-inline" data-tooltip="Admin">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                      <path d="M12 1L15.5 8.5L23 9.5L17.5 15L19 23L12 19L5 23L6.5 15L1 9.5L8.5 8.5L12 1Z" stroke="currentColor" stroke-width="1.5"/>
                                      <circle cx="12" cy="10" r="1.5"/>
                                      <circle cx="7" cy="12" r="1.5"/>
                                      <circle cx="17" cy="12" r="1.5"/>
                                    </svg>
                                  </span>
                                @endif
                              </div>
                              <div class="account-email">{{ $acc->email }}</div>
                              @if($acc->id === $user->id)
                                <span class="sr-only">Active account</span>
                             @endif
                          </div>
                      </button>
                  @endforeach

                  <a href="{{ route('profile.accounts.create-child') }}" class="account-card add-account" title="Add account">
                      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                          <rect x="1" y="1" width="22" height="22" rx="6" fill="rgba(255,255,255,0.03)"/>
                          <path d="M12 7v10M7 12h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="account-info">
                          <div class="account-name">Create account</div>
                          <div class="account-email">Link to main</div>
                      </div>
                  </a>
              </div>

              <p class="text-sm text-gray-400 mt-2">Switch between your linked accounts. Creating a new account from here links it to your main account.</p>
          </div>

          <hr class="my-6 border-t" />

          <form method="POST" action="{{ route('profile.destroy') }}"
                onsubmit="return confirm('Delete your account? This cannot be undone.');">
            @csrf
            @method('DELETE')

            <div class="text-sm text-gray-500 mb-2">
              Provide current password to confirm deletion (optional)
            </div>

            <input type="password" name="current_password"
                   placeholder="Current password" class="input-field">

            <div class="mt-3">
              <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white">
                Delete account
              </button>
            </div>
          </form>
        </section>
      </div>
    </div>
  </div>
</main>
@endsection
