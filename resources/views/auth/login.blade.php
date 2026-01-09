<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('auth.login') }} — ShopLy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root {
            --bg: #0b0b0b;
            --card: #171717;
            --border: #2a2a2a;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --accent: #f59e0b;
            --error: #ef4444;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(ellipse at center, #111 0%, #000 70%);
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--text);
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: linear-gradient(180deg, #1a1a1a, #141414);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 30px 80px rgba(0,0,0,.8);
            border: 1px solid var(--border);
        }

        .brand { text-align: center; margin-bottom: 24px; }
        .brand h1 { margin: 0; font-size: 20px; font-weight: 600; }
        .brand p { margin: 6px 0 0; font-size: 14px; color: var(--muted); }

        .field { margin-bottom: 16px; }
        label { display:block; font-size:13px; margin-bottom:6px; color:var(--muted); }

        input[type="email"],
        input[type="password"] {
            width:100%; height:42px; padding:0 12px;
            background:#0f0f0f; border:1px solid var(--border);
            border-radius:10px; color:var(--text); font-size:14px;
        }
        input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 1px rgba(245,158,11,.4); }

        .error { margin-top:6px; font-size:12px; color:var(--error); }

        .actions { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .remember { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:13px; }

        button {
            width:100%; height:44px; margin-top:8px; border:none; border-radius:12px;
            background:var(--accent); color:#000; font-size:14px; font-weight:600; cursor:pointer;
        }
        button:hover { filter:brightness(1.05); }

        .footer { margin-top:20px; text-align:center; font-size:13px; }
        .footer a { color:var(--accent); text-decoration:none; }
        .footer a:hover { text-decoration:underline; }

        .divider { display:flex; align-items:center; gap:12px; margin:20px 0; color:var(--muted); font-size:13px; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border); }

        .google-btn, .github-btn {
            display:flex; align-items:center; justify-content:center; gap:10px;
            width:100%; height:44px; border:1px solid var(--border); border-radius:12px;
            background:transparent; color:var(--text); font-size:14px; font-weight:500;
            cursor:pointer; text-decoration:none; transition: all 0.2s ease;
            margin-bottom:10px;
        }
        .google-btn:hover, .github-btn:hover { background:rgba(255,255,255,0.05); border-color:var(--muted); }
        .google-btn svg, .github-btn svg { width:20px; height:20px; }
        .github-btn { margin-bottom:0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>ShopLy</h1>
            <p>{{ __('auth.login_title') }}</p>
        </div>

        @if(session('error'))
            <div style="margin-bottom:16px; padding:12px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:8px; color:#ef4444; font-size:13px;">
                {{ session('error') }}
            </div>
        @endif

        <a href="{{ route('auth.google') }}" class="google-btn">
            <svg viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            {{ __('auth.continue_with_google') }}
        </a>

        <a href="{{ route('auth.github') }}" class="github-btn">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
            </svg>
            {{ __('auth.continue_with_github') }}
        </a>

        <div class="divider">{{ __('auth.or') }}</div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">{{ __('auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">{{ __('auth.password_field') }}</label>
                <input id="password" name="password" type="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="actions">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    {{ __('auth.remember_me') }}
                </label>
                <a href="{{ route('password.request') }}" style="color:var(--accent); text-decoration:none">{{ __('auth.forgot_password') }}</a>
            </div>

            <div>
                <button type="submit">{{ __('auth.sign_in') }}</button>
            </div>
        </form>

        <div class="footer">
            <a href="{{ route('register') }}">{{ __('auth.no_account') }} {{ __('auth.create_one') }}</a>
        </div>
    </div>
</body>
</html>