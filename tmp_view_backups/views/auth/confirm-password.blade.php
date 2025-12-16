<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Confirm Password — ShopLy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1>ShopLy</h1>
            <p>Confirm your password</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required autofocus>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div>
                <button type="submit">Confirm password</button>
            </div>
        </form>

        <div class="footer">
            <a href="{{ route('login') }}">Back to login</a>
        </div>
    </div>
</body>
</html>