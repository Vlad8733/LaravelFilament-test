<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $r)
    {
        $q = $r->input('query', $r->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $prods = Product::with(['images', 'category', 'company'])->where('is_active', true)
            ->where(fn ($qb) => $qb->where('name', 'like', '%'.$q.'%')->orWhere('description', 'like', '%'.$q.'%'))
            ->limit(8)->get();

        $prodRes = $prods->map(function ($p) {
            $img = null;
            if ($p->images && $p->images->count() > 0) {
                $first = $p->images->first();
                if ($first && $first->image_path) {
                    $path = $first->image_path;
                    if (strpos($path, 'public/') === 0) {
                        $path = substr($path, 7);
                    }
                    $img = asset('storage/'.$path);
                }
            }

            return ['type' => 'product', 'id' => $p->id, 'name' => $p->name, 'price' => $p->sale_price ?? $p->price,
                'image' => $img, 'url' => route('products.show', $p->slug), 'company' => $p->company?->name];
        });

        $comps = Company::where('is_active', true)
            ->where(fn ($qb) => $qb->where('name', 'like', '%'.$q.'%')->orWhere('description', 'like', '%'.$q.'%')->orWhere('short_description', 'like', '%'.$q.'%'))
            ->withCount(['products' => fn ($qb) => $qb->where('is_active', true)])->limit(4)->get();

        $compRes = $comps->map(fn ($c) => [
            'type' => 'company', 'id' => $c->id, 'name' => $c->name, 'image' => $c->logo_url,
            'url' => route('companies.show', $c->slug), 'products_count' => $c->products_count, 'is_verified' => $c->is_verified,
        ]);

        return response()->json($compRes->merge($prodRes));
    }
}
