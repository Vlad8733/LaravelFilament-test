<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register ShopLy</title>
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

        * {
            box-sizing: border-box;
        }

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

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .brand p {
            margin: 6px 0 0;
            font-size: 14px;
            color: var(--muted);
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: var(--muted);
        }

        input {
            width: 100%;
            height: 42px;
            padding: 0 12px;
            background: #0f0f0f;
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 1px rgba(245, 158, 11, .4);
        }

        .error {
            margin-top: 6px;
            font-size: 12px;
            color: var(--error);
        }

        button {
            width: 100%;
            height: 44px;
            margin-top: 8px;
            border: none;
            border-radius: 12px;
            background: var(--accent);
            color: #000;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            filter: brightness(1.05);
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }

        .footer a {
            color: var(--accent);
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h2>ShopLy</h2>
            <p>Create account</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="field">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>

            <button type="submit">
                Register
            </button>
        </form>

        <div class="footer">
            <a href="{{ route('login') }}">Already registered? Sign in</a>
        </div>
    </div>
</body>
</html>
