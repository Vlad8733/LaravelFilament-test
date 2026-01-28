<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function download(Order $o)
    {
        $u = Auth::user();
        if ($o->user_id === null) {
            if (session('last_order_id') != $o->id) {
                abort(403, __('invoice.errors.access_denied'));
            }

            return $this->genPdf($o);
        }
        if (! $u) {
            return redirect()->route('login');
        }
        if ($u->id !== $o->user_id && ! $u->isAdmin()) {
            abort(403, __('invoice.errors.access_denied'));
        }

        return $this->genPdf($o);
    }

    public function downloadByNumber(Request $r, string $num)
    {
        $r->validate(['email' => 'required|email']);
        $o = Order::where('order_number', $num)->firstOrFail();
        if (strtolower($o->customer_email) !== strtolower($r->email)) {
            abort(403, __('invoice.errors.email_mismatch'));
        }

        return $this->genPdf($o);
    }

    public function view(Order $o)
    {
        $u = Auth::user();
        if ($o->user_id === null) {
            if (session('last_order_id') != $o->id) {
                abort(403, __('invoice.errors.access_denied'));
            }

            return $this->genPdf($o, false);
        }
        if (! $u) {
            return redirect()->route('login');
        }
        if ($u->id !== $o->user_id && ! $u->isAdmin()) {
            abort(403, __('invoice.errors.access_denied'));
        }

        return $this->genPdf($o, false);
    }

    private function genPdf(Order $o, bool $dl = true)
    {
        $o->load(['items.product', 'user', 'status']);
        $pdf = Pdf::loadView('invoices.template', ['order' => $o, 'company' => config('invoice.company'), 'generated_at' => now()]);
        $cfg = config('invoice.pdf', []);
        $pdf->setPaper($cfg['paper'] ?? 'a4', $cfg['orientation'] ?? 'portrait');
        $fn = 'invoice-'.$o->order_number.'.pdf';

        return $dl ? $pdf->download($fn) : $pdf->stream($fn);
    }
}
