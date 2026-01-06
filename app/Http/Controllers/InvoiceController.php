<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Скачать инвойс заказа (для авторизованных пользователей или гостевых заказов)
     */
    public function download(Order $order)
    {
        $user = Auth::user();

        // Гостевой заказ (user_id = null) - разрешаем всем
        if ($order->user_id === null) {
            return $this->generatePdf($order);
        }

        // Для заказов с user_id - требуем авторизацию
        if (! $user) {
            return redirect()->route('login');
        }

        // Проверка: только владелец или админ
        if ($user->id !== $order->user_id && ! $user->isAdmin()) {
            abort(403, __('invoice.errors.access_denied'));
        }

        return $this->generatePdf($order);
    }

    /**
     * Скачать инвойс по номеру заказа (публичный доступ)
     */
    public function downloadByNumber(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        return $this->generatePdf($order);
    }

    /**
     * Просмотр инвойса в браузере
     */
    public function view(Order $order)
    {
        $user = Auth::user();

        // Гостевой заказ - разрешаем
        if ($order->user_id === null) {
            return $this->generatePdf($order, false);
        }

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->id !== $order->user_id && ! $user->isAdmin()) {
            abort(403, __('invoice.errors.access_denied'));
        }

        return $this->generatePdf($order, false);
    }

    /**
     * Генерация PDF
     */
    private function generatePdf(Order $order, bool $download = true)
    {
        $order->load(['items.product', 'user', 'status']);

        $data = [
            'order' => $order,
            'company' => [
                'name' => 'ShopLy',
                'address' => '123 Commerce Street',
                'city' => 'Business City, BC 12345',
                'country' => 'United States',
                'phone' => '+1 (555) 123-4567',
                'email' => 'support@shoply.com',
                'website' => 'www.shoply.com',
            ],
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('invoices.template', $data);

        $pdf->setPaper('a4', 'portrait');

        $filename = 'invoice-'.$order->order_number.'.pdf';

        if ($download) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
