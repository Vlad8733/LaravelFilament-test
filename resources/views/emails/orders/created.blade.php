@component('mail::message')
# 🎉 {{ __('notifications.order_confirmed_title') }}

{{ __('notifications.order_created_greeting', ['name' => $order->customer_name]) }}

{{ __('notifications.order_created_thank_you') }}

---

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
<tr>
<td style="background-color: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td width="50%" style="padding: 12px 0; border-bottom: 1px solid #334155;">
<span style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('notifications.order_number') }}</span><br>
<span style="color: #f1f5f9; font-size: 18px; font-weight: 600;">{{ $order->order_number }}</span>
</td>
<td width="50%" style="padding: 12px 0; border-bottom: 1px solid #334155; text-align: right;">
<span style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('notifications.order_date') }}</span><br>
<span style="color: #f1f5f9; font-size: 18px; font-weight: 600;">{{ $order->created_at->format('M d, Y') }}</span>
</td>
</tr>
<tr>
<td width="50%" style="padding: 12px 0;">
<span style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('notifications.order_status') }}</span><br>
<span style="color: #22c55e; font-size: 18px; font-weight: 600;">{{ __('notifications.status_confirmed') }}</span>
</td>
<td width="50%" style="padding: 12px 0; text-align: right;">
<span style="color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('notifications.order_total') }}</span><br>
<span style="color: #3b82f6; font-size: 22px; font-weight: 700;">${{ number_format($order->total, 2) }}</span>
</td>
</tr>
</table>
</td>
</tr>
</table>

---

@component('mail::button', ['url' => route('orders.tracking.show', $order->order_number), 'color' => 'primary'])
{{ __('notifications.view_order_action') }}
@endcomponent

{{ __('notifications.order_created_processing') }}

{{ __('notifications.email_salutation') }}
@endcomponent
