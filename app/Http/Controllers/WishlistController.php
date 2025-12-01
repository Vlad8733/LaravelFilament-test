<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $sessionId = session()->getId();
        
        $wishlistItems = Wishlist::with(['product.images', 'product.category'])
            ->where('session_id', $sessionId)
            ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function add(Request $request, $productId)
    {
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $sessionId = session()->getId();
        
        $existingItem = Wishlist::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();
            
        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product already in wishlist'
            ]);
        }

        Wishlist::create([
            'session_id' => $sessionId,
            'product_id' => $productId,
        ]);

        $wishlistCount = Wishlist::where('session_id', $sessionId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist!',
            'wishlistCount' => $wishlistCount
        ]);
    }

    public function remove(Request $request, $productId)
    {
        $sessionId = session()->getId();
        
        $wishlistItem = Wishlist::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();
            
        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in wishlist'
            ], 404);
        }

        $wishlistItem->delete();
        
        $wishlistCount = Wishlist::where('session_id', $sessionId)->count();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
            'wishlistCount' => $wishlistCount
        ]);
    }

    public function getCount()
    {
        $sessionId = session()->getId();
        $count = Wishlist::where('session_id', $sessionId)->count();
        
        return response()->json(['count' => $count]);
    }

    public function getItems()
    {
        $sessionId = session()->getId();
        $items = Wishlist::where('session_id', $sessionId)->pluck('product_id');
        
        return response()->json(['items' => $items]);
    }
}