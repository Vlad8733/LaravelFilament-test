<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $r)
    {
        $q = Product::with(['category', 'images'])->active();

        if ($r->filled('category') && $r->category !== 'all') {
            is_numeric($r->category) ? $q->where('category_id', $r->category) : $q->whereHas('category', fn ($qb) => $qb->where('slug', $r->category));
        }
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where(fn ($qb) => $qb->where('name', 'LIKE', "%{$s}%")->orWhere('description', 'LIKE', "%{$s}%")->orWhere('long_description', 'LIKE', "%{$s}%"));
        }
        if ($r->filled('price_min')) {
            $q->whereRaw('COALESCE(sale_price, price) >= ?', [$r->price_min]);
        }
        if ($r->filled('price_max')) {
            $q->whereRaw('COALESCE(sale_price, price) <= ?', [$r->price_max]);
        }
        if ($r->filled('in_stock') && $r->in_stock) {
            $q->inStock();
        }
        if ($r->filled('on_sale') && $r->on_sale) {
            $q->whereNotNull('sale_price');
        }

        match ($r->sort) {
            'price_asc' => $q->orderByRaw('COALESCE(sale_price, price) ASC'),
            'price_desc' => $q->orderByRaw('COALESCE(sale_price, price) DESC'),
            'name_asc' => $q->orderBy('name', 'asc'),
            'name_desc' => $q->orderBy('name', 'desc'),
            'newest' => $q->orderBy('created_at', 'desc'),
            'rating' => $q->withAvg('reviews', 'rating')->orderBy('reviews_avg_rating', 'desc'),
            'popular' => $q->withCount('orderItems')->orderBy('order_items_count', 'desc'),
            default => $q->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc'),
        };

        return view('products.index', [
            'products' => $q->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->withCount('products')->get(),
            'stats' => ['total_products' => Product::active()->count(), 'price_range' => ['min' => Product::active()->min('price'), 'max' => Product::active()->max('price')]],
        ]);
    }

    public function show(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }
        $product->load(['category', 'images', 'reviews' => fn ($q) => $q->approved()->with('user')->orderBy('created_at', 'desc')]);

        $related = Product::where('category_id', $product->category_id)->where('id', '!=', $product->id)->active()->inStock()->with(['images'])->take(4)->get();
        $alsoBought = Product::whereHas('orderItems.order.items', fn ($q) => $q->where('product_id', $product->id))
            ->where('id', '!=', $product->id)->active()->inStock()->withCount('orderItems')->orderBy('order_items_count', 'desc')->take(4)->get();

        return view('products.show', ['product' => $product, 'images' => $product->images()->orderBy('sort_order')->get(), 'relatedProducts' => $related, 'alsoBought' => $alsoBought]);
    }

    public function category(Category $category)
    {
        return view('products.category', ['category' => $category, 'products' => $category->products()->active()->with(['images'])->paginate(12)]);
    }

    public function search(Request $r)
    {
        $q = $r->get('q');
        if (! $q) {
            return response()->json([]);
        }

        $prods = Product::active()->where(fn ($qb) => $qb->where('name', 'LIKE', "%{$q}%")->orWhere('description', 'LIKE', "%{$q}%"))
            ->with(['images'])->take(8)->get()->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug,
                'price' => $p->getCurrentPrice(), 'image' => $p->getPrimaryImage()?->image_url, 'url' => route('products.show', $p),
            ]);

        return response()->json($prods);
    }
}
