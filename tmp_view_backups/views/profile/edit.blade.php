@extends('layouts.app')

@section('content')
<div class="site-wrapper">
<!DOCTYPE html>
<html lang="en" x-data>
<head>
  <meta charset="utf-8">
  <title>Profile — ShopLy</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="/css/app.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
  
</head>
<body class="bg-gray-50">

  <!-- Navigation (same as products page) -->
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo -->
        <div class="flex items-center">
          <a href="{{ route('products.index') }}" class="text-2xl font-bold text-blue-600">MyShop</a>
        </div>

        <!-- Search (readonly on profile, but kept for consistency) -->
        <div class="flex-1 max-w-lg mx-8">
          <div class="relative">
            <input type="text"
                   placeholder="Search products..."
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>

        <!-- Cart, Wishlist & Actions -->
        <div class="flex items-center space-x-4">
          <a href="{{ route('wishlist.index') }}" class="relative p-2 text-gray-600 hover:text-gray-900">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
          </a>

          <a href="{{ route('cart.show') }}" class="relative p-2 text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M280-80q-33 0-56.5-23.5T200-160q0-33 23.5-56.5T280-240q33 0 56.5 23.5T360-160q0 33-23.5 56.5T280-80Zm400 0q-33 0-56.5-23.5T600-160q0-33 23.5-56.5T680-240q33 0 56.5 23.5T760-160q0 33-23.5 56.5T680-80ZM246-720l96 200h280l110-200H246Zm-38-80h590q23 0 35 20.5t1 41.5L692-482q-11 20-29.5 31T622-440H324l-44 80h480v80H280q-45 0-68-39.5t-2-78.5l54-98-144-304H40v-80h130l38 80Zm134 280h280-280Z"/></svg>
          </a>

          <a href="{{ route('checkout.show') }}" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg text-center font-medium hover:bg-blue-700 transition-colors">
            Proceed to Checkout
          </a>

          @auth
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-2 text-gray-600 hover:text-gray-900">
              <img src="{{ auth()->user()->avatar ? asset('storage/' . auth()->user()->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(auth()->user()->email))) . '?s=40&d=identicon' }}" alt="avatar" class="w-8 h-8 rounded-full object-cover border">
              <span class="hidden sm:inline text-sm">{{ auth()->user()->name }}</span>
            </a>
          @else
            <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">Login</a>
            <a href="{{ route('register') }}" class="ml-3 text-sm text-blue-600 hover:underline">Register</a>
          @endauth
        </div>
      </div>
    </div>
  </nav>

  <!-- Profile content (kept layout & styles) -->
  <main class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @if(session('status'))<div class="mb-4 text-green-600">{{ session('status') }}</div>@endif

      <div class="profile-card">
        <div style="display:flex;gap:1rem;flex-wrap:wrap">
          <!-- Avatar panel -->
          <aside style="width:280px">
            <div style="text-align:center;margin-bottom:12px">
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

              <div style="margin-top:12px">
                <button class="btn-primary" type="submit">Upload avatar</button>
              </div>
            </form>

            <div style="margin-top:18px" class="settings-card">
              <div style="margin-bottom:8px; font-weight:700">Theme</div>
              <div style="display:flex;gap:8px">
                <button id="themeToggle" class="btn-primary" type="button" onclick="toggleTheme()">Theme</button>
                <small style="align-self:center;color:var(--muted)">Stored locally</small>
              </div>
            </div>
          </aside>

          <!-- Account panel -->
          <section style="flex:1">
            <form method="POST" action="{{ route('profile.update') }}">
              @csrf

              <div>
                <label class="field-label">Name</label>
                <input type="text" name="name" value="{{ old('name',$user->name) }}" class="input-field" required>
                @error('name') <div class="error">{{ $message }}</div> @enderror
              </div>

              <div style="margin-top:12px">
                <label class="field-label">Email</label>
                <input type="email" name="email" value="{{ old('email',$user->email) }}" class="input-field" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
              </div>

              <div style="margin-top:12px">
                <label class="field-label">New password (leave blank to keep)</label>
                <input type="password" name="password" class="input-field" autocomplete="new-password">
                @error('password') <div class="error">{{ $message }}</div> @enderror
              </div>

              <div style="margin-top:12px">
                <label class="field-label">Confirm password</label>
                <input type="password" name="password_confirmation" class="input-field" autocomplete="new-password">
              </div>

              <div style="margin-top:16px">
                <button class="btn-primary" type="submit">Save account</button>
              </div>
            </form>

            <hr class="my-6 border-t" />

            <div>
              <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Delete your account? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <div class="text-sm text-gray-500 mb-2">Provide current password to confirm deletion (optional)</div>
                <input type="password" name="current_password" placeholder="Current password" class="input-field">
                <div style="margin-top:12px">
                  <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white">Delete account</button>
                </div>
              </form>
            </div>
          </section>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Consolidated: avatar preview + robust theme toggler
    (function(){
      // Avatar preview
      const avatarInput = document.getElementById('avatarInput');
      const avatarPreview = document.getElementById('avatarPreview');
      if (avatarInput) {
        avatarInput.addEventListener('change', (e)=>{
          const file = e.target.files[0];
          if (!file) return;
          const reader = new FileReader();
          reader.onload = function(ev){
            avatarPreview.innerHTML = '<img src="' + ev.target.result + '" alt="avatar">';
          };
          reader.readAsDataURL(file);
        });
      }

      // Theme logic
      const btn = document.getElementById('themeToggle');
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

      function applyTheme(choice) {
        const html = document.documentElement;
        html.classList.remove('theme-light','theme-dark');
        if (choice === 'system') {
          const prefersDark = mediaQuery.matches;
          html.classList.add(prefersDark ? 'theme-dark' : 'theme-light');
          html.dataset.theme = 'system';
        } else {
          html.classList.add(choice === 'dark' ? 'theme-dark' : 'theme-light');
          html.dataset.theme = choice;
        }
        localStorage.setItem('site_theme', choice);
        if (btn) btn.textContent = 'Theme: ' + choice;
      }

      // Toggle exposed for onclick fallback
      window.toggleTheme = function(){
        const cur = localStorage.getItem('site_theme') || 'system';
        const next = cur === 'system' ? 'dark' : (cur === 'dark' ? 'light' : 'system');
        applyTheme(next);
      };

      // react to system changes when in 'system' mode
      try {
        mediaQuery.addEventListener?.('change', ()=> {
          if ((localStorage.getItem('site_theme') || 'system') === 'system') {
            applyTheme('system');
          }
        });
      } catch(e) {}

      // attach click listener (if button exists)
      if (btn) {
        btn.addEventListener('click', () => { window.toggleTheme(); });
      }

      // initialize
      const stored = localStorage.getItem('site_theme') || 'system';
      applyTheme(stored);
    })();
  </script>
</body>
</html>
@endsection