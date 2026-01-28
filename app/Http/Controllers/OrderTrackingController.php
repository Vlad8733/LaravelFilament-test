<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(Request $r, $num)
    {
        $o = Order::with(['status', 'statusHistory.status', 'statusHistory.changedBy', 'items.product'])->where('order_number', $num)->firstOrFail();
        $email = $r->query('email');

        if (auth()->check()) {
            if (auth()->user()->email !== $o->customer_email) {
                abort(403, 'You do not have permission to view this order');
            }
        } elseif (! $email || $email !== $o->customer_email) {
            return view('orders.tracking-auth', ['orderNumber' => $num]);
        }

        return view('orders.tracking', ['order' => $o, 'allStatuses' => \App\Models\OrderStatus::where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function search(Request $r)
    {
        $r->validate(['order_number' => 'required|string', 'email' => 'required|email']);
        $o = Order::where('order_number', $r->order_number)->where('customer_email', $r->email)->first();

        return $o ? redirect()->route('orders.tracking.show', ['orderNumber' => $o->order_number]) : back()->withErrors(['order_number' => 'Order not found with this email address.']);
    }
}
