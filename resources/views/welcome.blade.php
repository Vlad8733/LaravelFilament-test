<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ config('app.name', 'e-Shop') }} — Welcome</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #030305;
            --bg-secondary: #0a0a0c;
            --surface: #111114;
            --surface-elevated: #18181b;
            --border: rgba(255,255,255,0.06);
            --border-subtle: rgba(255,255,255,0.03);
            --text-primary: #fafafa;
            --text-secondary: #a1a1aa;
            --text-muted: #71717a;
            --accent: #f59e0b;
            --accent-glow: rgba(245,158,11,0.15);
            --success: #22c55e;
            --gradient-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(245,158,11,0.08) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(34,197,94,0.04) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(139,92,246,0.03) 0%, transparent 50%);
            animation: bgFloat 20s ease-in-out infinite;
        }

        @keyframes bgFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(2%, 2%) rotate(1deg); }
            66% { transform: translate(-1%, 1%) rotate(-1deg); }
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }

        /* Main layout */
        .container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .header {
            padding: 24px 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--gradient-amber);
            display: grid;
            place-items: center;
            font-weight: 800;
            font-size: 16px;
            color: #000;
            box-shadow: 0 8px 32px var(--accent-glow);
        }

        .logo-text {
            font-weight: 700;
            font-size: 20px;
            color: var(--text-primary);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .lang-switch {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 12px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-secondary);
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .lang-switch:hover {
            background: var(--surface-elevated);
            color: var(--text-primary);
        }

        /* Hero section */
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 48px 80px;
        }

        .hero-content {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .hero-text {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(245,158,11,0.05));
            border: 1px solid rgba(245,158,11,0.2);
            border-radius: 100px;
            width: fit-content;
            font-size: 13px;
            font-weight: 500;
            color: var(--accent);
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }

        .hero-title {
            font-size: clamp(42px, 5vw, 64px);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .hero-title .highlight {
            background: var(--gradient-amber);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-description {
            font-size: 18px;
            line-height: 1.7;
            color: var(--text-secondary);
            max-width: 520px;
        }

        .hero-actions {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--gradient-amber);
            color: #000;
            box-shadow: 0 8px 32px var(--accent-glow), 0 2px 8px rgba(0,0,0,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px var(--accent-glow), 0 4px 12px rgba(0,0,0,0.4);
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--text-primary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--surface-elevated);
            border-color: rgba(255,255,255,0.1);
        }

        .btn svg {
            width: 18px;
            height: 18px;
        }

        /* Stats */
        .stats {
            display: flex;
            gap: 40px;
            padding-top: 16px;
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* Cards section */
        .hero-cards {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .feature-card {
            background: linear-gradient(180deg, var(--surface), var(--bg-secondary));
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 28px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: rgba(255,255,255,0.1);
            transform: translateX(8px);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .feature-icon.amber {
            background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(245,158,11,0.05));
            border: 1px solid rgba(245,158,11,0.2);
        }

        .feature-icon.green {
            background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(34,197,94,0.05));
            border: 1px solid rgba(34,197,94,0.2);
        }

        .feature-icon.purple {
            background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(139,92,246,0.05));
            border: 1px solid rgba(139,92,246,0.2);
        }

        .feature-icon.blue {
            background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(59,130,246,0.05));
            border: 1px solid rgba(59,130,246,0.2);
        }

        .feature-content h3 {
            font-size: 17px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text-primary);
        }

        .feature-content p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Floating elements */
        .floating-card {
            position: absolute;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Footer */
        .footer {
            padding: 24px 48px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-text {
            font-size: 13px;
            color: var(--text-muted);
        }

        .footer-links {
            display: flex;
            gap: 24px;
        }

        .footer-links a {
            font-size: 13px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 48px;
                text-align: center;
            }

            .hero-text {
                align-items: center;
            }

            .hero-description {
                max-width: 600px;
            }

            .hero-actions {
                justify-content: center;
            }

            .stats {
                justify-content: center;
            }

            .hero-cards {
                max-width: 500px;
                margin: 0 auto;
            }

            .feature-card:hover {
                transform: translateY(-4px);
            }
        }

        @media (max-width: 640px) {
            .header {
                padding: 16px 20px;
            }

            .hero {
                padding: 24px 20px 48px;
            }

            .stats {
                flex-wrap: wrap;
                gap: 24px;
            }

            .hero-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }

            .footer {
                flex-direction: column;
                gap: 16px;
                text-align: center;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-pattern">
        <div class="grid-overlay"></div>
    </div>

    <div class="container">
        <!-- Header -->
        <header class="header">
            <a href="/" class="logo">
                <div class="logo-icon">eS</div>
                <span class="logo-text"><span style="color: #ffffff;">e-</span><span style="color: #f59e0b;">Shop</span></span>
            </a>
            <div class="header-actions">
                @php
                    $currentLocale = app()->getLocale();
                    $locales = ['en' => 'EN', 'ru' => 'RU', 'lv' => 'LV'];
                    $nextLocale = match($currentLocale) {
                        'en' => 'ru',
                        'ru' => 'lv',
                        'lv' => 'en',
                        default => 'en'
                    };
                @endphp
                <a href="/language/{{ $nextLocale }}" class="lang-switch">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    {{ $locales[$nextLocale] }}
                </a>
            </div>
        </header>

        <!-- Hero -->
        <main class="hero">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="badge">
                        <span class="badge-dot"></span>
                        {{ __('welcome.badge') }}
                    </div>

                    <h1 class="hero-title">
                        {{ __('welcome.title_start') }}
                        <span class="highlight">{{ __('welcome.title_highlight') }}</span>
                        {{ __('welcome.title_end') }}
                    </h1>

                    <p class="hero-description">
                        {{ __('welcome.description') }}
                    </p>

                    <div class="hero-actions">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/products') }}" class="btn btn-primary">
                                    {{ __('welcome.browse_products') }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    {{ __('welcome.login') }}
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="btn btn-secondary">
                                        {{ __('welcome.register') }}
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>

                    <div class="stats">
                        <div class="stat">
                            <div class="stat-value">10K+</div>
                            <div class="stat-label">{{ __('welcome.stat_products') }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">50K+</div>
                            <div class="stat-label">{{ __('welcome.stat_customers') }}</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value">99%</div>
                            <div class="stat-label">{{ __('welcome.stat_satisfaction') }}</div>
                        </div>
                    </div>
                </div>

                <div class="hero-cards">
                    <div class="feature-card">
                        <div class="feature-icon amber">⚡</div>
                        <div class="feature-content">
                            <h3>{{ __('welcome.feature_performance_title') }}</h3>
                            <p>{{ __('welcome.feature_performance_desc') }}</p>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon green">🛒</div>
                        <div class="feature-content">
                            <h3>{{ __('welcome.feature_cart_title') }}</h3>
                            <p>{{ __('welcome.feature_cart_desc') }}</p>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon purple">💳</div>
                        <div class="feature-content">
                            <h3>{{ __('welcome.feature_payments_title') }}</h3>
                            <p>{{ __('welcome.feature_payments_desc') }}</p>
                        </div>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon blue">⭐</div>
                        <div class="feature-content">
                            <h3>{{ __('welcome.feature_reviews_title') }}</h3>
                            <p>{{ __('welcome.feature_reviews_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-text">
                © {{ date('Y') }} e-Shop. {{ __('welcome.footer_rights') }}
            </div>
            <div class="footer-links">
                <a href="https://github.com/Vlad8733/LaravelFilament-test.git">{{ __('welcome.footer_github') }}</a>
                <a href="/support">{{ __('welcome.footer_support') }}</a>
            </div>
        </footer>
    </div>
</body>
</html>
