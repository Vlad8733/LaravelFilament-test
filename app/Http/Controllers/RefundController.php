<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    /**
     * Показать список возвратов пользователя
     */
    public function index()
    {
        $refunds = RefundRequest::with(['order', 'statusHistory'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('refunds.index', compact('refunds'));
    }

    /**
     * Форма создания запроса на возврат
     */
    public function create(Order $order)
    {
        // Проверяем что заказ принадлежит пользователю
        if ($order->customer_email !== Auth::user()->email) {
            abort(403, 'You do not have permission to request a refund for this order.');
        }

        // Проверяем что заказ уже доставлен или обработан
        $allowedStatuses = ['delivered', 'completed', 'shipped'];
        if (! in_array($order->status->slug ?? '', $allowedStatuses)) {
            return back()->with('error', 'Refund can only be requested for delivered orders.');
        }

        // Проверяем что нет активного запроса на возврат
        $existingRefund = RefundRequest::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRefund) {
            return redirect()->route('refunds.show', $existingRefund)
                ->with('info', 'You already have an active refund request for this order.');
        }

        return view('refunds.create', compact('order'));
    }

    /**
     * Сохранить запрос на возврат
     */
    public function store(Request $request, Order $order)
    {
        // Проверяем что заказ принадлежит пользователю
        if ($order->customer_email !== Auth::user()->email) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:full,partial',
            'amount' => 'required_if:type,partial|nullable|numeric|min:0.01|max:'.$order->total,
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $amount = $request->type === 'full' ? $order->total : $request->amount;

        $refund = RefundRequest::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'status' => 'pending',
            'type' => $request->type,
            'amount' => $amount,
            'reason' => $request->reason,
        ]);

        // Добавляем запись в историю
        $refund->addStatusHistory('pending', __('refunds.note_pending'), Auth::id());

        return redirect()->route('refunds.show', $refund)
            ->with('success', __('refunds.request_submitted'));
    }

    /**
     * Показать детали запроса на возврат
     */
    public function show(RefundRequest $refund)
    {
        // Проверяем доступ
        if ($refund->user_id !== Auth::id()) {
            abort(403);
        }

        $refund->load(['order.items.product', 'statusHistory.changedByUser']);

        return view('refunds.show', compact('refund'));
    }

    /**
     * Отменить запрос на возврат (только если pending)
     */
    public function cancel(RefundRequest $refund)
    {
        if ($refund->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $refund->isPending()) {
            return back()->with('error', __('refunds.error_only_pending_can_cancel'));
        }

        $refund->update(['status' => 'rejected']);
        $refund->addStatusHistory('rejected', __('refunds.note_cancelled_by_customer'), Auth::id());

        return redirect()->route('refunds.index')
            ->with('success', __('refunds.request_cancelled'));
    }
}
