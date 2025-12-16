<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'MyShop') }}</title>

    <!-- Подключаем скомпилированный CSS/JS (версия из вывода npm run build) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BWS3gT0v.css') }}">
    <script src="{{ asset('build/assets/app-CAiCLEjY.js') }}" defer></script>

    <!-- Alpine (если нужно) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
</head>
<body>
    @yield('content')
</body>
</html>