<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RefundController extends Controller
{
    public function index()
    {
        $refunds = RefundRequest::with(['order', 'statusHistory'])->where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();

        return view('refunds.index', ['refunds' => $refunds]);
    }

    public function create(Order $o)
    {
        if ($o->customer_email !== Auth::user()->email) {
            abort(403, 'No permission');
        }

        $allowed = ['delivered', 'completed', 'shipped'];
        if (! in_array($o->status->slug ?? '', $allowed)) {
            return back()->with('error', 'Refund can only be requested for delivered orders.');
        }

        $existing = RefundRequest::where('order_id', $o->id)->whereIn('status', ['pending', 'approved'])->first();
        if ($existing) {
            return redirect()->route('refunds.show', $existing)->with('info', 'You already have an active refund request.');
        }

        return view('refunds.create', ['order' => $o]);
    }

    public function store(Request $r, Order $o)
    {
        if ($o->customer_email !== Auth::user()->email) {
            abort(403);
        }

        $r->validate([
            'type' => 'required|in:full,partial',
            'amount' => 'required_if:type,partial|nullable|numeric|min:0.01|max:'.$o->total,
            'reason' => 'required|string|min:10|max:1000',
        ]);

        $amt = $r->type === 'full' ? $o->total : $r->amount;
        $ref = RefundRequest::create(['order_id' => $o->id, 'user_id' => Auth::id(), 'status' => 'pending', 'type' => $r->type, 'amount' => $amt, 'reason' => $r->reason]);
        $ref->addStatusHistory('pending', __('refunds.note_pending'), Auth::id());

        return redirect()->route('refunds.show', $ref)->with('success', __('refunds.request_submitted'));
    }

    public function show(RefundRequest $refund)
    {
        if ($refund->user_id !== Auth::id()) {
            abort(403);
        }
        $refund->load(['order.items.product', 'statusHistory.changedByUser']);

        return view('refunds.show', ['refund' => $refund]);
    }

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

        return redirect()->route('refunds.index')->with('success', __('refunds.request_cancelled'));
    }
}
