<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportController extends Controller
{
    public function export()
    {
        $fileName = 'products_export_'.date('Ymd_His').'.csv';

        $response = new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // headers
            fputcsv($handle, ['id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock_quantity', 'category', 'is_active', 'is_featured', 'created_at']);

            Product::with(['category'])->chunk(200, function ($products) use ($handle) {
                foreach ($products as $p) {
                    fputcsv($handle, [
                        $p->id,
                        $p->name,
                        $p->slug,
                        $p->sku,
                        $p->price,
                        $p->sale_price,
                        $p->stock_quantity,
                        optional($p->category)->name,
                        $p->is_active ? '1' : '0',
                        $p->is_featured ? '1' : '0',
                        $p->created_at->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}
