<?php

namespace App\Http\Controllers;

use App\Models\ProductComparison;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompareController extends Controller
{
    /**
     * Display comparison page
     */
    public function index()
    {
        $items = ProductComparison::getItems();
        
        // Load products with their relationships directly from items
        $products = $items->map(function ($item) {
            return $item->product->load('category', 'images');
        })->filter(); // filter out any null products

        // Collect all unique attributes for comparison
        $attributes = [];
        foreach ($products as $product) {
            if ($product->specifications) {
                $specs = is_array($product->specifications) ? $product->specifications : json_decode($product->specifications, true);
                if ($specs) {
                    foreach ($specs as $key => $value) {
                        if (!in_array($key, $attributes)) {
                            $attributes[] = $key;
                        }
                    }
                }
            }
        }

        return view('compare.index', compact('products', 'attributes'));
    }

    /**
     * Get comparison count
     */
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => ProductComparison::getCount()
        ]);
    }

    /**
     * Get comparison items (for dropdown)
     */
    public function items(): JsonResponse
    {
        $items = ProductComparison::getItems();
        
        $products = $items->map(function ($item) {
            $product = $item->product;
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->getCurrentPrice(),
                'image' => $product->getPrimaryImage() 
                    ? asset('storage/' . $product->getPrimaryImage()->image_path) 
                    : null,
                'url' => route('products.show', $product->slug),
            ];
        });

        return response()->json([
            'products' => $products,
            'count' => $items->count()
        ]);
    }

    /**
     * Add product to comparison
     */
    public function add(int $productId): JsonResponse
    {
        $result = ProductComparison::addProduct($productId);
        return response()->json($result);
    }

    /**
     * Remove product from comparison
     */
    public function remove(int $productId): JsonResponse
    {
        $result = ProductComparison::removeProduct($productId);
        return response()->json($result);
    }

    /**
     * Clear all comparison items
     */
    public function clear(): JsonResponse
    {
        $result = ProductComparison::clearAll();
        return response()->json($result);
    }

    /**
     * Toggle product in comparison
     */
    public function toggle(int $productId): JsonResponse
    {
        if (ProductComparison::hasProduct($productId)) {
            return $this->remove($productId);
        }
        return $this->add($productId);
    }
}
