<?php

namespace App\Http\Controllers;

use App\Models\CustomerReview;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerReviewController extends Controller
{
    /**
     * Список отзывов пользователя
     */
    public function index()
    {
        $reviews = CustomerReview::with(['order', 'product.images'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reviews.index', compact('reviews'));
    }

    /**
     * Форма создания отзыва
     */
    public function create(Order $order)
    {
        // Проверяем что заказ принадлежит пользователю
        if ($order->customer_email !== Auth::user()->email) {
            abort(403, 'You do not have permission to review this order.');
        }

        // Проверяем что заказ доставлен
        if (! $order->canBeReviewed()) {
            return back()->with('error', 'You can only review delivered orders.');
        }

        // Загружаем items с продуктами и существующими отзывами
        $order->load(['items.product.images']);

        // Получаем уже оставленные отзывы
        $existingReviews = CustomerReview::where('order_id', $order->id)
            ->where('user_id', Auth::id())
            ->pluck('product_id')
            ->toArray();

        // Фильтруем items без отзывов
        $itemsToReview = $order->items->filter(function ($item) use ($existingReviews) {
            return ! in_array($item->product_id, $existingReviews);
        });

        if ($itemsToReview->isEmpty()) {
            return redirect()->route('reviews.index')
                ->with('info', 'You have already reviewed all products in this order.');
        }

        return view('reviews.create', compact('order', 'itemsToReview'));
    }

    /**
     * Сохранить отзыв
     */
    public function store(Request $request, Order $order)
    {
        // Проверяем что заказ принадлежит пользователю
        if ($order->customer_email !== Auth::user()->email) {
            abort(403);
        }

        // Проверяем что заказ доставлен
        if (! $order->canBeReviewed()) {
            return back()->with('error', 'You can only review delivered orders.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'delivery_rating' => 'required|integer|min:1|max:5',
            'packaging_rating' => 'required|integer|min:1|max:5',
            'product_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Проверяем что продукт есть в заказе
        $orderItem = $order->items()->where('product_id', $request->product_id)->first();
        if (! $orderItem) {
            return back()->with('error', 'This product is not in your order.');
        }

        // Проверяем что отзыв ещё не оставлен
        $existingReview = CustomerReview::where('order_id', $order->id)
            ->where('product_id', $request->product_id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        CustomerReview::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'delivery_rating' => $request->delivery_rating,
            'packaging_rating' => $request->packaging_rating,
            'product_rating' => $request->product_rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        // Проверяем есть ли ещё продукты для отзыва
        $existingReviews = CustomerReview::where('order_id', $order->id)
            ->where('user_id', Auth::id())
            ->pluck('product_id')
            ->toArray();

        $remainingItems = $order->items->filter(function ($item) use ($existingReviews) {
            return ! in_array($item->product_id, $existingReviews);
        });

        if ($remainingItems->isNotEmpty()) {
            return redirect()->route('reviews.create', $order)
                ->with('success', 'Review submitted! You can review more products.');
        }

        return redirect()->route('reviews.index')
            ->with('success', 'Thank you for your reviews! They will be published after moderation.');
    }

    /**
     * Показать отзыв
     */
    public function show(CustomerReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->load(['order', 'product.images']);

        return view('reviews.show', compact('review'));
    }

    /**
     * Форма редактирования отзыва
     */
    public function edit(CustomerReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        // Можно редактировать только pending отзывы
        if (! $review->isPending()) {
            return back()->with('error', 'You can only edit pending reviews.');
        }

        $review->load(['order', 'product.images']);

        return view('reviews.edit', compact('review'));
    }

    /**
     * Обновить отзыв
     */
    public function update(Request $request, CustomerReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $review->isPending()) {
            return back()->with('error', 'You can only edit pending reviews.');
        }

        $request->validate([
            'delivery_rating' => 'required|integer|min:1|max:5',
            'packaging_rating' => 'required|integer|min:1|max:5',
            'product_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        $review->update([
            'delivery_rating' => $request->delivery_rating,
            'packaging_rating' => $request->packaging_rating,
            'product_rating' => $request->product_rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('reviews.show', $review)
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Удалить отзыв
     */
    public function destroy(CustomerReview $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403);
        }

        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully.');
    }
}
