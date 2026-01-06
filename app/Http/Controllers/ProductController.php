<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'images'])
            ->active();

        // Фильтрация по категории
        if ($request->filled('category') && $request->category !== 'all') {
            if (is_numeric($request->category)) {
                $query->where('category_id', $request->category);
            } else {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }
        }

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhere('long_description', 'LIKE', "%{$search}%");
            });
        }

        // Фильтр по цене
        if ($request->filled('price_min')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$request->price_min]);
        }
        if ($request->filled('price_max')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$request->price_max]);
        }

        // Фильтр по наличию
        if ($request->filled('in_stock') && $request->in_stock) {
            $query->inStock();
        }

        // Фильтр по акциям
        if ($request->filled('on_sale') && $request->on_sale) {
            $query->whereNotNull('sale_price');
        }

        // Сортировка
        switch ($request->sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(sale_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(sale_price, price) DESC');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')
                    ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'popular':
                $query->withCount('orderItems')
                    ->orderBy('order_items_count', 'desc');
                break;
            default:
                $query->orderBy('is_featured', 'desc')
                    ->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('is_active', true)->withCount('products')->get();

        // Статистика для фильтров
        $stats = [
            'total_products' => Product::active()->count(),
            'price_range' => [
                'min' => Product::active()->min('price'),
                'max' => Product::active()->max('price'),
            ],
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
    }

    public function show(Product $product)
    {
        if (! $product->is_active) {
            abort(404);
        }

        $product->load([
            'category',
            'images',
            'reviews' => function ($query) {
                $query->approved()
                    ->with('user')
                    ->orderBy('created_at', 'desc');
            },
        ]);

        // Получаем изображения продукта
        $images = $product->images()->orderBy('sort_order')->get();

        // Похожие товары
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->with(['images'])
            ->take(4)
            ->get();

        // Также покупают
        $alsoBought = Product::whereHas('orderItems.order.items', function ($query) use ($product) {
            $query->where('product_id', $product->id);
        })
            ->where('id', '!=', $product->id)
            ->active()
            ->inStock()
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'images', 'relatedProducts', 'alsoBought'));
    }

    public function category(Category $category)
    {
        $products = $category->products()
            ->active()
            ->with(['images'])
            ->paginate(12);

        return view('products.category', compact('category', 'products'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        if (! $query) {
            return response()->json([]);
        }

        $products = Product::active()
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->with(['images'])
            ->take(8)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => $product->getCurrentPrice(),
                    'image' => $product->getPrimaryImage()?->image_url,
                    'url' => route('products.show', $product),
                ];
            });

        return response()->json($products);
    }
}
