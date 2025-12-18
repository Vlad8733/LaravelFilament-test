<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with(['images', 'category'])
            ->where('name', 'like', '%' . $query . '%')
            ->orWhere('description', 'like', '%' . $query . '%')
            ->limit(10)
            ->get();

        $results = $products->map(function ($product) {
            $image = null;
            
            // Get first image if exists
            if ($product->images && $product->images->count() > 0) {
                $firstImage = $product->images->first();
                if ($firstImage && $firstImage->image_path) {
                    // Ensure proper path format
                    $imagePath = $firstImage->image_path;
                    
                    // Remove 'public/' prefix if present (storage handles this)
                    if (strpos($imagePath, 'public/') === 0) {
                        $imagePath = substr($imagePath, 7);
                    }
                    
                    $image = asset('storage/' . $imagePath);
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price ?? $product->price,
                'image' => $image,
                'url' => route('products.show', $product->id)
            ];
        });

        return response()->json($results);
    }
}