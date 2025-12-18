<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class SearchController extends Controller
{
    /**
     * Global product search for autocompletion / instant results.
     * Accepts query param names: query, search, q
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('query', $request->query('search', $request->query('q', ''))));

        // minimum length to avoid heavy queries / spam
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        try {
            // basic fulltext-like matching over name / description / long_description
            $items = Product::query()
                ->where('name', 'LIKE', "%{$q}%")
                ->orWhere('description', 'LIKE', "%{$q}%")
                ->limit(8)
                ->get(['id','name','slug','price','image'])
                ->map(function ($p) {
                    return [
                        'id'    => $p->id,
                        'name'  => $p->name,
                        'slug'  => $p->slug,
                        'price' => $p->price,
                        // попытка получить URL изображения (адаптируй, если у вас другое поле/рел)
                        'image' => $p->image ? asset('storage/' . $p->image) : null,
                        'url'   => route('products.show', $p->id),
                    ];
                })->values();

            return response()->json($items);
        } catch (\Throwable $e) {
            \Log::error('SearchController error: '.$e->getMessage());
            return response()->json([]);
        }
    }
}