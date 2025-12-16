<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password — ShopLy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>ShopLy</h1>
            <p>Reset your password</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <button type="submit">Send reset link</button>
            </div>
        </form>

        <div class="footer">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>
</body>
</html>