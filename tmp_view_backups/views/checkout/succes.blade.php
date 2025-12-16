<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - My Shop</title>
    <link href="/css/app.css" rel="stylesheet">
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 mb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('products.index') }}" class="text-2xl font-bold text-blue-600">MyShop</a>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-8 text-center">
            <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Order Placed Successfully!</h1>
            
            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <p class="text-lg text-gray-700 mb-2">
                    Thank you, <strong>{{ $order->customer_name }}</strong>!
                </p>
                <p class="text-gray-600 mb-4">
                    Your order has been received and is being processed.
                </p>
                <div class="space-y-2 text-sm text-gray-600">
                    <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                    <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                </div>
            </div>
            
            <div class="space-y-3">
                <p class="text-gray-600">
                    We'll send you a confirmation email with tracking information.
                </p>
                <a href="{{ route('products.index') }}" 
                   class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>

</body>
</html>