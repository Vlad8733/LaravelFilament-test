<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $r, Product $product)
    {
        $r->validate([
            'reviewer_name' => 'required|string|max:255', 'reviewer_email' => 'required|email|max:255',
            'rating' => 'required|integer|min:1|max:5', 'comment' => 'required|string|max:1000',
        ]);

        $rev = Review::create([
            'product_id' => $product->id, 'reviewer_name' => $r->reviewer_name, 'reviewer_email' => $r->reviewer_email,
            'rating' => $r->rating, 'comment' => $r->comment, 'is_approved' => false,
        ]);

        $msg = 'Thank you for your review! It will be published after moderation.';

        return $r->ajax() ? response()->json(['success' => true, 'message' => $msg, 'review' => $rev]) : redirect()->back()->with('success', $msg);
    }
}
