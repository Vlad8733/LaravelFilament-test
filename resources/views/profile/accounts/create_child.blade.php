<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="theme-dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('create-child-account.title') }} - ShopLy</title>
    
    <!-- Apply theme before CSS loads to prevent flash -->
    <script>
        (function() {
            var theme = localStorage.getItem('settings_theme') || 'dark';
            if (theme === 'auto') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.classList.remove('theme-dark', 'theme-light');
            document.documentElement.classList.add('theme-' + theme);
        })();
    </script>

    <style>
        /* ============================================
           CSS Variables - Theme Support
           ============================================ */
        :root {
            /* Dark theme (default) */
            --bg-primary: #0a0a0a;
            --bg-secondary: #111111;
            --bg-card: linear-gradient(145deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.02) 100%);
            --bg-input: rgba(0, 0, 0, 0.4);
            --border-color: rgba(255, 255, 255, 0.1);
            --border-focus: rgba(245, 158, 11, 0.5);
            --text-primary: #ffffff;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --accent: #f59e0b;
            --accent-hover: #d97706;
            --accent-soft: rgba(245, 158, 11, 0.15);
            --error: #ef4444;
            --error-soft: rgba(239, 68, 68, 0.15);
            --success: #10b981;
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .theme-light {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-card: linear-gradient(145deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            --bg-input: rgba(0, 0, 0, 0.03);
            --border-color: rgba(0, 0, 0, 0.1);
            --border-focus: rgba(245, 158, 11, 0.6);
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        /* ============================================
           Base Styles
           ============================================ */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-primary);
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Background decoration */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(ellipse at 30% 20%, rgba(245, 158, 11, 0.08) 0%, transparent 50%),
                        radial-gradient(ellipse at 70% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ============================================
           Card Container
           ============================================ */
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 480px;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow);
        }

        /* ============================================
           Header / Brand
           ============================================ */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: var(--accent-soft);
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .brand-logo svg {
            width: 28px;
            height: 28px;
            color: var(--accent);
        }

        .brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .brand p {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* ============================================
           Master Account Info
           ============================================ */
        .master-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            margin-bottom: 28px;
        }

        .master-label {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .master-name {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .master-avatar {
            width: 32px;
            height: 32px;
            background: var(--accent-soft);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-weight: 700;
            font-size: 0.8125rem;
        }

        .max-badge {
            padding: 6px 12px;
            background: var(--accent-soft);
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent);
            white-space: nowrap;
        }

        /* ============================================
           Form Styles
           ============================================ */
        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }

        input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 44px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        input::placeholder {
            color: var(--text-muted);
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        input:focus + svg,
        .input-wrapper:focus-within svg {
            color: var(--accent);
        }

        .field-hint {
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .field-error {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8125rem;
            color: var(--error);
            margin-top: 4px;
        }

        .field-error svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* ============================================
           Buttons
           ============================================ */
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            height: 52px;
            padding: 0 24px;
            border-radius: 14px;
            font-size: 0.9375rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
            color: #0a0a0a;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary svg {
            width: 18px;
            height: 18px;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background: var(--bg-input);
            border-color: var(--text-muted);
            color: var(--text-primary);
        }

        /* ============================================
           Theme Toggle
           ============================================ */
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }

        .theme-toggle:hover {
            background: var(--accent-soft);
            border-color: var(--accent);
        }

        .theme-toggle svg {
            width: 20px;
            height: 20px;
            color: var(--text-secondary);
            transition: color 0.2s;
        }

        .theme-toggle:hover svg {
            color: var(--accent);
        }

        .theme-light .icon-sun { display: none; }
        .theme-dark .icon-moon { display: none; }

        /* ============================================
           Language Switcher
           ============================================ */
        .lang-switch {
            position: absolute;
            top: 20px;
            left: 20px;
            display: flex;
            gap: 4px;
            padding: 4px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            z-index: 10;
        }

        .lang-btn {
            padding: 6px 12px;
            background: transparent;
            border: none;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .lang-btn:hover {
            color: var(--text-primary);
        }

        .lang-btn.active {
            background: var(--accent);
            color: #0a0a0a;
        }

        /* ============================================
           Footer Link
           ============================================ */
        .footer-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
        }

        .footer-link a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .footer-link a:hover {
            color: var(--accent);
        }

        .footer-link svg {
            width: 16px;
            height: 16px;
        }

        /* ============================================
           Responsive
           ============================================ */
        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .card {
                padding: 28px 20px;
                border-radius: 20px;
            }

            .field-row {
                grid-template-columns: 1fr;
            }

            .master-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .theme-toggle {
                top: 12px;
                right: 12px;
            }

            .lang-switch {
                top: 12px;
                left: 12px;
            }
        }

        /* ============================================
           Animations
           ============================================ */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</head>
<body>
    <!-- Language Switcher -->
    <div class="lang-switch">
        <a href="{{ route('language.switch', 'en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
        <a href="{{ route('language.switch', 'ru') }}" class="lang-btn {{ app()->getLocale() === 'ru' ? 'active' : '' }}">RU</a>
    </div>

    <!-- Theme Toggle -->
    <button type="button" class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">
        <svg class="icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <svg class="icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
    </button>

    <div class="card" role="main">
        <!-- Header -->
        <div class="brand">
            <div class="brand-logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1>{{ __('create-child-account.title') }}</h1>
            <p>{{ __('create-child-account.subtitle') }}</p>
        </div>

        <!-- Master Account Info -->
        <div class="master-info">
            <div>
                <div class="master-label">{{ __('create-child-account.master_account') }}</div>
                <div class="master-name">
                    <span class="master-avatar">{{ strtoupper(substr($master->name ?? auth()->user()->name, 0, 1)) }}</span>
                    {{ $master->name ?? auth()->user()->name }}
                </div>
            </div>
            <span class="max-badge">{{ __('create-child-account.max_children', ['count' => 2]) }}</span>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('profile.accounts.store-child') }}">
            @csrf

            <div class="form-grid">
                <!-- Full Name -->
                <div class="field">
                    <label for="name">{{ __('create-child-account.full_name') }}</label>
                    <div class="input-wrapper">
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            value="{{ old('name') }}" 
                            placeholder="{{ __('create-child-account.full_name') }}"
                            required 
                            autofocus
                        >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    @error('name')
                        <div class="field-error">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="field">
                    <label for="email">{{ __('create-child-account.email') }}</label>
                    <div class="input-wrapper">
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            value="{{ old('email') }}" 
                            placeholder="{{ __('create-child-account.email') }}"
                            required
                        >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @error('email')
                        <div class="field-error">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password Row -->
                <div class="field-row">
                    <div class="field">
                        <label for="password">{{ __('create-child-account.password') }}</label>
                        <div class="input-wrapper">
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                placeholder="••••••••"
                                required
                            >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        @error('password')
                            <div class="field-error">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">{{ __('create-child-account.password_confirmation') }}</label>
                        <div class="input-wrapper">
                            <input 
                                id="password_confirmation" 
                                name="password_confirmation" 
                                type="password" 
                                placeholder="••••••••"
                                required
                            >
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Username (Optional) -->
                <div class="field">
                    <label for="username">{{ __('create-child-account.username') }}</label>
                    <div class="input-wrapper">
                        <input 
                            id="username" 
                            name="username" 
                            type="text" 
                            value="{{ old('username') }}" 
                            placeholder="@username"
                        >
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                        </svg>
                    </div>
                    @error('username')
                        <div class="field-error">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        {{ __('create-child-account.create_btn') }}
                    </button>
                    
                    <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
                        {{ __('create-child-account.cancel') }}
                    </a>
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="footer-link">
            <a href="{{ route('profile.edit') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ __('create-child-account.back_to_profile') }}
            </a>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('theme-dark');
            const newTheme = isDark ? 'light' : 'dark';
            
            html.classList.remove('theme-dark', 'theme-light');
            html.classList.add('theme-' + newTheme);
            localStorage.setItem('settings_theme', newTheme);
        }
    </script>
</body>
</html>
