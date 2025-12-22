<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice {{ $order->order_number }}</title>
    
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #1f2937;
            background: #ffffff;
        }

        /* Container */
        .invoice-container {
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header */
        .invoice-header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 3px solid #f59e0b;
            padding-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 32px;
            font-weight: bold;
            color: #f59e0b;
            margin-bottom: 5px;
        }

        .company-tagline {
            color: #6b7280;
            font-size: 11px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .invoice-number {
            font-size: 14px;
            color: #6b7280;
        }

        /* Info Section */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .info-block {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding-right: 20px;
        }

        .info-block:last-child {
            padding-right: 0;
            text-align: right;
        }

        .info-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .info-value {
            font-size: 12px;
            color: #1f2937;
            line-height: 1.6;
        }

        .info-value strong {
            font-weight: 600;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }

        .status-paid {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table thead tr {
            background: #f3f4f6;
        }

        .items-table th {
            padding: 12px 15px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .items-table th:nth-child(2),
        .items-table td:nth-child(2) {
            text-align: center;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            text-align: right;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .items-table tbody tr:hover {
            background: #f9fafb;
        }

        .product-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }

        .product-sku {
            font-size: 10px;
            color: #9ca3af;
        }

        /* Totals */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .totals-spacer {
            display: table-cell;
            width: 50%;
        }

        .totals-box {
            display: table-cell;
            width: 50%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table tr td {
            padding: 8px 0;
        }

        .totals-table tr td:last-child {
            text-align: right;
            font-weight: 500;
        }

        .totals-table tr.subtotal td {
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
        }

        .totals-table tr.discount td {
            color: #10b981;
        }

        .totals-table tr.total {
            font-size: 16px;
            font-weight: bold;
        }

        .totals-table tr.total td {
            padding-top: 12px;
            border-top: 2px solid #1f2937;
            color: #1f2937;
        }

        .totals-table tr.total td:last-child {
            color: #f59e0b;
        }

        /* Notes & Footer */
        .notes-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .notes-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .notes-content {
            font-size: 11px;
            color: #4b5563;
            line-height: 1.6;
        }

        /* Footer */
        .invoice-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .footer-thanks {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .footer-contact {
            font-size: 11px;
            color: #6b7280;
        }

        .footer-contact a {
            color: #f59e0b;
            text-decoration: none;
        }

        /* Watermark for PAID orders */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(16, 185, 129, 0.08);
            text-transform: uppercase;
            letter-spacing: 20px;
            pointer-events: none;
            z-index: -1;
        }

        /* QR Code placeholder */
        .qr-section {
            text-align: center;
            margin-top: 20px;
        }

        .qr-code {
            display: inline-block;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 8px;
        }

        .qr-label {
            font-size: 9px;
            color: #9ca3af;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    @if($order->payment_status === 'paid')
        <div class="watermark">PAID</div>
    @endif

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-left">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-tagline">Your Premium Shopping Destination</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $order->order_number }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-block">
                <div class="info-label">Bill To</div>
                <div class="info-value">
                    <strong>{{ $order->customer_name }}</strong><br>
                    {{ $order->customer_email }}<br>
                    @if($order->shipping_address)
                        {{ $order->shipping_address }}
                    @endif
                </div>
            </div>
            
            <div class="info-block">
                <div class="info-label">From</div>
                <div class="info-value">
                    <strong>{{ $company['name'] }}</strong><br>
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}<br>
                    {{ $company['email'] }}
                </div>
            </div>
            
            <div class="info-block">
                <div class="info-label">Invoice Details</div>
                <div class="info-value">
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                    <strong>Due:</strong> {{ $order->created_at->addDays(7)->format('M d, Y') }}<br>
                    <strong>Payment:</strong> {{ ucfirst($order->payment_method ?? 'N/A') }}
                </div>
                <span class="status-badge {{ $order->payment_status === 'paid' ? 'status-paid' : ($order->payment_status === 'pending' ? 'status-pending' : 'status-failed') }}">
                    {{ strtoupper($order->payment_status ?? 'Pending') }}
                </span>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Product</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 17%;">Unit Price</th>
                    <th style="width: 18%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="product-name">{{ $item->product_name }}</div>
                            @if($item->product && $item->product->sku)
                                <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                            @endif
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->product_price, 2) }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-spacer"></div>
            <div class="totals-box">
                <table class="totals-table">
                    <tr class="subtotal">
                        <td>Subtotal</td>
                        <td>${{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    @if($order->discount_amount > 0)
                        <tr class="discount">
                            <td>Discount @if($order->coupon_code)({{ $order->coupon_code }})@endif</td>
                            <td>-${{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Shipping</td>
                        <td>Free</td>
                    </tr>
                    <tr class="total">
                        <td>Total</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
            <div class="notes-section">
                <div class="notes-title">Order Notes</div>
                <div class="notes-content">{{ $order->notes }}</div>
            </div>
        @endif

        <div class="notes-section">
            <div class="notes-title">Terms & Conditions</div>
            <div class="notes-content">
                Thank you for shopping with {{ $company['name'] }}! If you have any questions about this invoice, 
                please contact our support team at {{ $company['email'] }}. Refund requests must be submitted 
                within 30 days of purchase.
            </div>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="footer-thanks">Thank you for your business!</div>
            <div class="footer-contact">
                {{ $company['phone'] }} | <a href="mailto:{{ $company['email'] }}">{{ $company['email'] }}</a> | {{ $company['website'] }}
            </div>
            <div class="qr-section">
                <div class="qr-label">Track your order at: {{ url('/track-order/' . $order->order_number) }}</div>
            </div>
        </div>
    </div>
</body>
</html>
