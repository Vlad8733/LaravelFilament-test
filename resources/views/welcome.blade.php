<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>{{ config('app.name', 'ShopLy') }} — Welcome</title>
    <style>
        :root{
            --bg-1: #050507;
            --bg-2: #0f0f10;
            --card-1: #0f1418;
            --card-2: #121316;
            --muted: #9ca3af;
            --accent: #f59e0b;
            --accent-dark: #b36f05;
            --glass: rgba(255,255,255,0.04);
            --glass-2: rgba(255,255,255,0.02);
            --success: #10b981;
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            background: radial-gradient(1200px 500px at 10% 10%, rgba(245,158,11,0.06), transparent 6%),
                        radial-gradient(1000px 400px at 95% 90%, rgba(16,185,129,0.03), transparent 6%),
                        linear-gradient(180deg,var(--bg-1),var(--bg-2));
            color:#e6eef6;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:36px;
        }

        .shell{
            width:100%;
            max-width:1100px;
            border-radius:16px;
            overflow:hidden;
            display:grid;
            grid-template-columns: 1fr 460px;
            gap:0;
            box-shadow: 0 10px 40px rgba(2,6,23,0.6);
            border: 1px solid rgba(255,255,255,0.03);
            background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent);
        }

        /* Left marketing panel */
        .panel{
            padding:48px;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.00)),
                linear-gradient(120deg, rgba(245,158,11,0.03), rgba(16,185,129,0.01));
            display:flex;
            flex-direction:column;
            justify-content:center;
            gap:20px;
            min-height:420px;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .logo{
            width:56px;
            height:56px;
            border-radius:12px;
            background:linear-gradient(135deg,var(--accent),var(--accent-dark));
            display:inline-grid;
            place-items:center;
            color:#081014;
            font-weight:800;
            font-size:18px;
            box-shadow: 0 6px 24px rgba(245,158,11,0.12), inset 0 -6px 18px rgba(255,255,255,0.04);
        }

        h1{
            margin:0;
            font-size:34px;
            letter-spacing:-0.02em;
            color:#f8fafc;
        }

        p.lead{
            margin:0;
            color:var(--muted);
            max-width:58ch;
            line-height:1.5;
        }

        .features{
            display:grid;
            grid-template-columns: repeat(2, minmax(0,1fr));
            gap:14px;
            margin-top:6px;
        }

        .feature{
            background: linear-gradient(180deg,var(--glass),var(--glass-2));
            border-radius:12px;
            padding:12px 14px;
            display:flex;
            gap:12px;
            align-items:flex-start;
            border: 1px solid rgba(255,255,255,0.02);
        }

        .feature .dot{
            width:36px;
            height:36px;
            border-radius:9px;
            display:grid;
            place-items:center;
            font-weight:700;
            color:#07101a;
            background:linear-gradient(180deg,var(--accent),var(--accent-dark));
            box-shadow: 0 6px 18px rgba(11,11,11,0.35);
        }

        .feature h4{margin:0;font-size:14px;color:#eff6ff}
        .feature p{margin:0;font-size:13px;color:var(--muted)}

        /* Right card */
        .card{
            background: linear-gradient(180deg,var(--card-1),var(--card-2));
            padding:32px;
            display:flex;
            flex-direction:column;
            gap:18px;
            border-left: 1px solid rgba(255,255,255,0.02);
        }

        .card h2{margin:0;font-size:20px}
        .card p{margin:0;color:var(--muted)}
        .actions{display:flex;gap:10px;margin-top:6px}

        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            padding:10px 14px;
            border-radius:10px;
            cursor:pointer;
            font-weight:600;
            font-size:14px;
            border: 1px solid rgba(255,255,255,0.03);
            text-decoration:none;
        }

        .btn-primary{
            background: linear-gradient(180deg,var(--accent),var(--accent-dark));
            color:#07101a;
            box-shadow: 0 10px 30px rgba(245,158,11,0.12);
        }

        .btn-ghost{
            background:transparent;
            color:#dfe7ee;
            border: 1px solid rgba(255,255,255,0.04);
        }

        .meta{
            display:flex;
            justify-content:space-between;
            gap:8px;
            align-items:center;
            margin-top:8px;
            color:var(--muted);
            font-size:13px;
        }

        .badge{
            background: rgba(255,255,255,0.03);
            padding:8px 10px;
            border-radius:999px;
            color: #e6eef6;
            font-weight:600;
            font-size:13px;
            border: 1px solid rgba(255,255,255,0.02);
        }

        .footer{
            margin-top:18px;
            font-size:13px;
            color:var(--muted);
            display:flex;
            gap:8px;
            align-items:center;
            justify-content:flex-start;
        }

        @media (max-width:980px){
            .shell{grid-template-columns:1fr; padding:0; border-radius:12px}
            .panel{padding:28px}
            .card{padding:20px}
        }
    </style>
</head>
<body>
    <div class="shell" role="main" aria-label="Welcome to ShopLy">
        <section class="panel">
            <div class="brand">
                <div class="logo">SL</div>
                <div>
                    <div style="font-size:13px;color:var(--muted);font-weight:600">Welcome to</div>
                    <div style="font-weight:800;font-size:18px">ShopLy</div>
                </div>
            </div>

            <h1></h1>
            <p class="lead">
                Fast, modern and designed for conversion — Ship products with confidence. Built-in cart, checkout,
                coupons, wishlist and payment integrations. Start your demo store in seconds or register to manage
                products and orders.
            </p>

            <div class="features" aria-hidden="false">
                <div class="feature">
                    <div class="dot">⚡</div>
                    <div>
                        <h4>Blazing performance</h4>
                        <p>Optimized front-end and minimal payloads for fast browsing.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="dot">🛒</div>
                    <div>
                        <h4>Cart & Checkout</h4>
                        <p>Seamless cart flow, coupons and demo payment for testing.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="dot">💳</div>
                    <div>
                        <h4>Payments</h4>
                        <p>Stripe & PayPal ready — mock or live payments supported.</p>
                    </div>
                </div>

                <div class="feature">
                    <div class="dot">⭐</div>
                    <div>
                        <h4>Customer Reviews</h4>
                        <p>Collect and display ratings to increase trust and conversions.</p>
                    </div>
                </div>
            </div>

            <div class="footer" aria-hidden="true">
                <span class="badge">Open Source Friendly</span>
                <span style="opacity:.7">•</span>
                <span style="color:var(--muted)">Made with care for developers & designers</span>
            </div>
        </section>

        <aside class="card" aria-labelledby="get-started">
            <div>
                <h2 id="get-started">Get started</h2>
                <p>Choose how you'd like to enter the store</p>
            </div>

            <div class="actions" role="group" aria-label="Primary actions">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/products') }}" class="btn btn-primary" title="products">Go to Home page</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary" title="Log in">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-ghost" title="Register">Create account</a>
                        @endif
                    @endauth
                @endif
            </div>

            <div class="meta" aria-hidden="false">
                <div>
                    <div style="font-weight:700">Demo store</div>
                    <div style="color:var(--muted)">Seeded with example products</div>
                </div>
                <div style="text-align:right">
                    <div style="font-weight:700;color:var(--success)">$0</div>
                    <div style="color:var(--muted);font-size:12px">Free to try</div>
                </div>
            </div>

            <div style="border-top:1px solid rgba(255,255,255,0.02); margin-top:12px; padding-top:12px;">
            </div>

            <div style="margin-top:auto; color:var(--muted); font-size:13px;">
                <div style="margin-bottom:6px">Need help?</div>
                <div>Check docs or open an issue on the repo.</div>
            </div>
        </aside>
    </div>
</body>
</html>
