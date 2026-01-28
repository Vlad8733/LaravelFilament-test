<?php

namespace App\Http\Controllers;

use App\Models\ProductComparison;
use Illuminate\Http\JsonResponse;

class CompareController extends Controller
{
    public function index()
    {
        $items = ProductComparison::getItems();
        $prods = $items->map(fn ($i) => $i->product)->filter();
        $attrs = [];
        foreach ($prods as $p) {
            if (! $p->specifications) {
                continue;
            }
            $specs = is_array($p->specifications) ? $p->specifications : json_decode($p->specifications, true);
            if ($specs) {
                foreach ($specs as $k => $v) {
                    if (! in_array($k, $attrs)) {
                        $attrs[] = $k;
                    }
                }
            }
        }

        return view('compare.index', ['products' => $prods, 'attributes' => $attrs]);
    }

    public function count(): JsonResponse
    {
        return response()->json(['count' => ProductComparison::getCount()]);
    }

    public function items(): JsonResponse
    {
        $items = ProductComparison::getItems();
        $prods = $items->map(fn ($i) => [
            'id' => $i->product->id, 'name' => $i->product->name, 'price' => $i->product->getCurrentPrice(),
            'image' => $i->product->getPrimaryImage() ? asset('storage/'.$i->product->getPrimaryImage()->image_path) : null,
            'url' => route('products.show', $i->product->slug),
        ]);

        return response()->json(['products' => $prods, 'count' => $items->count()]);
    }

    public function add(int $pid): JsonResponse
    {
        return response()->json(ProductComparison::addProduct($pid));
    }

    public function remove(int $pid): JsonResponse
    {
        return response()->json(ProductComparison::removeProduct($pid));
    }

    public function clear(): JsonResponse
    {
        return response()->json(ProductComparison::clearAll());
    }

    public function toggle(int $pid): JsonResponse
    {
        $res = ProductComparison::hasProduct($pid) ? ProductComparison::removeProduct($pid) : ProductComparison::addProduct($pid);
        $res['action'] = ProductComparison::hasProduct($pid) ? 'added' : 'removed';

        return response()->json($res);
    }
}
