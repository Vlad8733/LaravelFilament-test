<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login ShopLy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>ShopLy</h1>
            <p>Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="actions">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" style="color:var(--accent); text-decoration:none">Forgot password?</a>
            </div>

            <div>
                <button type="submit">Sign in</button>
            </div>
        </form>

        <div class="footer">
            <a href="{{ route('register') }}">Don't have an account? Create one</a>
        </div>
    </div>
</body>
</html>