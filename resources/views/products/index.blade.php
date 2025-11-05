<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">

    <header class="mb-10">
        <h1 class="text-4xl font-bold text-center text-gray-800">Products</h1>
        <p class="text-center text-gray-600 mt-2">Browse our collection</p>
    </header>

    <main>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
                    <h2 class="text-2xl font-semibold mb-2 text-gray-800">{{ $product->name }}</h2>
                    <p class="text-gray-600 mb-4">{{ $product->description }}</p>
                    <p class="text-lg font-bold text-gray-900 mb-2">${{ number_format($product->price, 2) }}</p>
                    <p class="text-sm text-gray-500">Category: {{ ucfirst($product->category) }}</p>
                    <button class="mt-4 w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">Buy Now</button>
                </div>
            @endforeach
        </div>
    </main>

    <footer class="mt-12 text-center text-gray-500">
        &copy; {{ date('Y') }} My Shop. All rights reserved.
    </footer>

</body>
</html>
