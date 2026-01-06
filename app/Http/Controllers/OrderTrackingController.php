<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $request, $orderNumber)
    {
        // Ищем заказ по номеру
        $order = Order::with([
            'status',
            'statusHistory.status',
            'statusHistory.changedBy',
            'items.product',
        ])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Проверяем доступ: либо свой заказ, либо знаем email
        $email = $request->query('email');

        if (auth()->check()) {
            if (auth()->user()->email !== $order->customer_email) {
                abort(403, 'You do not have permission to view this order');
            }
        } elseif (! $email || $email !== $order->customer_email) {
            // Если не авторизован - показываем форму ввода email
            return view('orders.tracking-auth', [
                'orderNumber' => $orderNumber,
            ]);
        }

        // Получаем все возможные статусы для timeline
        $allStatuses = \App\Models\OrderStatus::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('orders.tracking', [
            'order' => $order,
            'allStatuses' => $allStatuses,
        ]);
    }

    public function search(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'email' => 'required|email',
        ]);

        $order = Order::where('order_number', $request->order_number)
            ->where('customer_email', $request->email)
            ->first();

        if (! $order) {
            return back()->withErrors([
                'order_number' => 'Order not found with this email address.',
            ]);
        }

        return redirect()->route('orders.tracking.show', [
            'orderNumber' => $order->order_number,
        ]);
    }
}
