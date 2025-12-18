<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // Show wishlist for current user
    public function index()
    {
        $userId = auth()->id();
        $wishlistItems = WishlistItem::with(['product.images', 'product.category'])
            ->where('user_id', $userId)
            ->get()
            ->map(function($wi) {
                // keep same structure as previous Wishlist model expected in blade
                $p = $wi->product;
                return (object)[
                    'id' => $wi->id,
                    'product' => $p,
                    'created_at' => $wi->created_at,
                ];
            });

        return view('wishlist.index', compact('wishlistItems'));
    }

    // Add product to wishlist (DB)
    public function add(Request $request, $productId)
    {
        $userId = auth()->id();
        if (! $userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $product = Product::find($productId);
        if (! $product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        WishlistItem::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json(['success' => true, 'message' => 'Added to wishlist']);
    }

    // Remove product from wishlist (DB)
    public function remove(Request $request, $productId)
    {
        $userId = auth()->id();
        WishlistItem::where('user_id', $userId)->where('product_id', $productId)->delete();
        return response()->json(['success' => true, 'message' => 'Removed']);
    }

    // Count for header/badge
    public function getCount()
    {
        $userId = auth()->id();
        $count = $userId ? WishlistItem::where('user_id', $userId)->count() : 0;
        return response()->json(['count' => $count]);
    }

    // Get product ids
    public function getItems()
    {
        $userId = auth()->id();
        $items = $userId ? WishlistItem::where('user_id', $userId)->pluck('product_id') : collect([]);
        return response()->json(['items' => $items]);
    }
}