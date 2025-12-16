<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register ShopLy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
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
