<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('create-child-account.title') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        :root{
            --bg:#0b0b0b;
            --card:#171717;
            --border:#2a2a2a;
            --text:#e5e7eb;
            --muted:#9ca3af;
            --accent:#f59e0b;
            --error:#ef4444;
        }

        *{box-sizing:border-box}
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:radial-gradient(ellipse at center,#111 0%,#000 70%);
            font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
            color:var(--text);
            padding:24px;
        }

        .card{
            width:100%;
            max-width:520px;
            background:linear-gradient(180deg,#1a1a1a,#141414);
            border-radius:14px;
            padding:28px;
            box-shadow:0 30px 80px rgba(0,0,0,.8);
            border:1px solid var(--border);
        }

        .brand{text-align:center;margin-bottom:18px}
        .brand h1{margin:0;font-size:20px;font-weight:600}
        .brand p{margin:6px 0 0;font-size:14px;color:var(--muted)}

        .field{margin-bottom:14px}
        label{display:block;font-size:13px;margin-bottom:6px;color:var(--muted)}
        input{
            width:100%;
            height:44px;
            padding:0 12px;
            background:#0f0f0f;
            border:1px solid var(--border);
            border-radius:10px;
            color:var(--text);
            font-size:14px;
        }
        input:focus{
            outline:none;
            border-color:var(--accent);
            box-shadow:0 0 0 1px rgba(245,158,11,.28);
        }

        .error{margin-top:6px;font-size:12px;color:var(--error)}
        .help{font-size:13px;color:var(--muted);margin-top:6px}

        button.primary{
            width:100%;
            height:46px;
            margin-top:8px;
            border:none;
            border-radius:12px;
            background:var(--accent);
            color:#071017;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
        }
        .actions{display:flex;gap:10px;margin-top:12px}
        a.cancel{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 14px;
            height:46px;
            border-radius:12px;
            background:transparent;
            border:1px solid var(--border);
            color:var(--text);
            text-decoration:none;
        }

        .meta{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:12px;
            margin-bottom:8px;
        }

        .parent-badge{
            background:linear-gradient(180deg,#111,#0d0d0d);
            border:1px solid rgba(255,255,255,0.03);
            padding:8px 12px;
            border-radius:10px;
            color:var(--muted);
            font-size:13px;
        }

        @media(max-width:560px){
            .card{padding:18px}
        }
    </style>
</head>
<body>
    <div class="card" role="main" aria-labelledby="create-linked-account">
        <div class="brand">
            <h1 id="create-linked-account">{{ __('create-child-account.title') }}</h1>
            <p>{{ __('create-child-account.subtitle') }}</p>
        </div>

        <div class="meta">
            <div>
                <div style="font-size:13px;color:var(--muted)">{{ __('create-child-account.master_account') }}</div>
                <div class="parent-badge">{{ $master->name ?? auth()->user()->name }}</div>
            </div>

            <div style="text-align:right;color:var(--muted);font-size:13px">
                {{ __('create-child-account.max_children', ['count' => 2]) }}
            </div>
        </div>

        <form method="POST" action="{{ route('profile.accounts.store-child') }}">
            @csrf

            <div class="field">
                <label for="name">{{ __('create-child-account.full_name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                @error('name') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="email">{{ __('create-child-account.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password">{{ __('create-child-account.password') }}</label>
                <input id="password" name="password" type="password" required>
                @error('password') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">{{ __('create-child-account.password_confirmation') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>

            <div class="field">
                <label for="username">{{ __('create-child-account.username') }}</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}">
                @error('username') <div class="error">{{ $message }}</div> @enderror
                <div class="help">{{ __('create-child-account.username_help') }}</div>
            </div>

            <button class="primary" type="submit">{{ __('create-child-account.create_btn') }}</button>

            <div class="actions">
                <a class="cancel" href="{{ route('profile.edit') }}">{{ __('create-child-account.cancel') }}</a>
            </div>
        </form>
    </div>
</body>
</html>