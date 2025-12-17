@extends('layouts.app')

@push('styles')
    @vite('resources/css/profile/profileedit.css')
@endpush

@push('scripts')
    @vite('resources/js/profile/profileedit.js')
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

          <div class="mt-4 settings-card">
            <div class="mb-2 font-bold">Theme</div>
            <div class="flex gap-2 items-center">
              <button id="themeToggle" class="btn-primary" type="button">Toggle theme</button>
              <small class="text-gray-500">Stored locally</small>
            </div>
          </div>
        </aside>

        <!-- Account panel -->
        <section class="flex-1">
          <form method="POST" action="{{ route('profile.update') }}">
            @csrf

            <div>
              <label class="field-label">Name</label>
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
