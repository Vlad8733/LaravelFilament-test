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

        $created = WishlistItem::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        activity_log('added_to_wishlist:' . __('activity_log.log.added_to_wishlist', ['product' => $product->name]));

        $count = WishlistItem::where('user_id', $userId)->count();

        return response()->json([
            'success' => true, 
            'message' => 'Added to wishlist',
            'count' => $count,
            'added' => $created->wasRecentlyCreated
        ]);
    }

    // Remove product from wishlist (DB)
    public function remove(Request $request, $productId)
    {
        $userId = auth()->id();
        if (! $userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $product = Product::find($productId);
        if (! $product) return response()->json(['success' => false, 'message' => 'Product not found'], 404);

        WishlistItem::where('user_id', $userId)->where('product_id', $productId)->delete();

        activity_log('removed_from_wishlist:' . __('activity_log.log.removed_from_wishlist', ['product' => $product->name]));

        $count = $userId ? WishlistItem::where('user_id', $userId)->count() : 0;
        
        return response()->json([
            'success' => true, 
            'message' => 'Removed',
            'count' => $count
        ]);
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