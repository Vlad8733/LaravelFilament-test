<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('query', $request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $products = Product::with(['images', 'category'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('description', 'like', '%'.$query.'%');
            })
            ->limit(10)
            ->get();

        $results = $products->map(function ($product) {
            $image = null;

            if ($product->images && $product->images->count() > 0) {
                $firstImage = $product->images->first();
                if ($firstImage && $firstImage->image_path) {
                    $imagePath = $firstImage->image_path;

                    if (strpos($imagePath, 'public/') === 0) {
                        $imagePath = substr($imagePath, 7);
                    }

                    $image = asset('storage/'.$imagePath);
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->sale_price ?? $product->price,
                'image' => $image,
                'url' => route('products.show', $product->slug),
            ];
        });

        return response()->json($results);
    }
}
