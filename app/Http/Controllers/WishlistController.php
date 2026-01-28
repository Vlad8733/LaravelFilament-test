<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $uid = auth()->id();
        $items = WishlistItem::with(['product.images', 'product.category', 'variant'])
            ->where('user_id', $uid)->get()
            ->map(fn ($wi) => (object) [
                'id' => $wi->id,
                'product' => $wi->product,
                'variant' => $wi->variant,
                'variant_id' => $wi->variant_id,
                'created_at' => $wi->created_at,
            ]);

        return view('wishlist.index', ['wishlistItems' => $items]);
    }

    public function add(Request $request, $productId)
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $variantId = $request->input('variant_id');
        $existing = WishlistItem::where('user_id', $uid)->where('product_id', $productId)->first();

        $wasCreated = false;
        if ($existing) {
            if ($variantId && $existing->variant_id != $variantId) {
                $existing->update(['variant_id' => $variantId]);
            }
        } else {
            WishlistItem::create(['user_id' => $uid, 'product_id' => $productId, 'variant_id' => $variantId]);
            $wasCreated = true;
        }

        activity_log('added_to_wishlist:'.__('activity_log.log.added_to_wishlist', ['product' => $product->name]));

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'count' => WishlistItem::where('user_id', $uid)->count(),
            'added' => $wasCreated,
        ]);
    }

    public function remove(Request $request, $productId)
    {
        $uid = auth()->id();
        if (! $uid) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $product = Product::find($productId);
        if (! $product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        WishlistItem::where('user_id', $uid)->where('product_id', $productId)->delete();
        activity_log('removed_from_wishlist:'.__('activity_log.log.removed_from_wishlist', ['product' => $product->name]));

        return response()->json([
            'success' => true,
            'message' => 'Removed',
            'count' => WishlistItem::where('user_id', $uid)->count(),
        ]);
    }

    public function getCount()
    {
        $uid = auth()->id();

        return response()->json(['count' => $uid ? WishlistItem::where('user_id', $uid)->count() : 0]);
    }

    public function getItems()
    {
        $uid = auth()->id();

        return response()->json(['items' => $uid ? WishlistItem::where('user_id', $uid)->pluck('product_id') : []]);
    }
}
