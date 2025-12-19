<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: #0a0a0a;
            color: #ffffff;
            font-family: system-ui, -apple-system, sans-serif;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-red-500 mb-4">403</h1>
        <p class="text-xl text-gray-400 mb-8">Access Denied - You don't have permission</p>
        <div class="flex gap-4 justify-center">
            <a href="/" class="px-6 py-3 bg-amber-500 text-black rounded-lg font-semibold hover:bg-amber-600 transition">
                Go to Home
            </a>
            <a href="/login" class="px-6 py-3 bg-gray-700 text-white rounded-lg font-semibold hover:bg-gray-600 transition">
                Login
            </a>
        </div>
    </div>
</body>
</html>