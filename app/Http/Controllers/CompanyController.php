<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyFollow;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $q = Company::query()->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])->withCount('followers');

        if ($s = $request->input('search')) {
            $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%"));
        }

        match ($request->input('sort', 'name')) {
            'products' => $q->orderByDesc('products_count'),
            'followers' => $q->orderByDesc('followers_count'),
            'newest' => $q->orderByDesc('created_at'),
            default => $q->orderBy('name'),
        };

        return view('companies.index', ['companies' => $q->paginate(12)]);
    }

    public function show(string $slug)
    {
        $c = Company::where('slug', $slug)->where('is_active', true)->withCount('followers')->firstOrFail();
        $prods = $c->products()->where('is_active', true)->with(['images', 'category'])->orderByDesc('created_at')->paginate(12);

        return view('companies.show', [
            'company' => $c, 'products' => $prods, 'isFollowing' => auth()->check() && $c->isFollowedBy(auth()->user()),
        ]);
    }

    public function toggleFollow(Request $r, Company $c)
    {
        $u = $r->user();
        if (! $u) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if ($c->user_id === $u->id) {
            return response()->json(['error' => 'Cannot follow your own company'], 400);
        }

        $f = CompanyFollow::where('user_id', $u->id)->where('company_id', $c->id)->first();
        if ($f) {
            $f->delete();
            $following = false;
        } else {
            CompanyFollow::create(['user_id' => $u->id, 'company_id' => $c->id]);
            $following = true;
        }

        return response()->json(['success' => true, 'is_following' => $following, 'followers_count' => $c->followers()->count()]);
    }
}
