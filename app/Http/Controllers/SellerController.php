<?php

namespace App\Http\Controllers;

use App\Models\Product;

class SellerController extends Controller
{
    public function index()
    {
        return view('seller.dashboard');
    }

    public function products()
    {
        $products = auth()->check() ? Product::where('user_id', auth()->id())->get() : collect();

        return view('seller.products', compact('products'));
    }
}
