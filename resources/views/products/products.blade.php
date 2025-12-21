@extends('layouts.app')

@section('title', __('products.title'))

@section('content')
@foreach($products as $product)
<div class="bg-white p-6 rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300">
    <h2 class="text-2xl font-semibold mb-2 text-gray-800">{{ $product->name }}</h2>
    <p class="text-gray-600 mb-4">{{ $product->description }}</p>
    <p class="text-lg font-bold text-gray-900 mb-2">${{ number_format($product->price, 2) }}</p>
    <p class="text-sm text-gray-500">{{ __('products.category') }}: {{ ucfirst($product->category) }}</p>

    <form action="{{ route('cart.add', $product) }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">
            {{ __('products.add_to_cart') }}
        </button>
    </form>
</div>
@endforeach
@endsection
