<?php

namespace App\Observers;

use App\Jobs\DispatchWebhookJob;
use App\Models\Order;
use App\Models\Webhook;
use App\Notifications\OrderCancelledNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function created(Order $o): void
    {
        Log::info('OrderObserver: Order created', ['order_id' => $o->id, 'order_number' => $o->order_number]);
        $this->dispatchWebhook(Webhook::EVENT_ORDER_CREATED, $o);
        $this->sendOrderCreatedNotification($o);
    }

    public function updated(Order $o): void
    {
        if (! $o->wasChanged('order_status')) {
            return;
        }

        $hooks = Webhook::forEvent(Webhook::EVENT_ORDER_STATUS_CHANGED);
        $payload = [
            'order_id' => $o->id, 'order_number' => $o->order_number,
            'old_status' => $o->getOriginal('order_status'), 'new_status' => $o->order_status,
            'customer_email' => $o->customer_email, 'total' => $o->total,
        ];
        foreach ($hooks as $h) {
            DispatchWebhookJob::dispatch($h, Webhook::EVENT_ORDER_STATUS_CHANGED, $payload);
        }
        $this->sendStatusChangeNotification($o);
    }

    protected function sendOrderCreatedNotification(Order $o): void
    {
        try {
            /** @var \App\Models\User|null $u */
            $u = $o->user;
            if ($o->user_id && $u) {
                $u->notify(new OrderCreatedNotification($o));
            }
            if ($o->customer_email && (! $u || $u->email !== $o->customer_email)) {
                Notification::route('mail', $o->customer_email)->notify(new OrderCreatedNotification($o));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send order created notification: '.$e->getMessage(), ['order_id' => $o->id]);
        }
    }

    protected function sendStatusChangeNotification(Order $o): void
    {
        $status = $o->order_status;
        try {
            $notif = match ($status) {
                'shipped' => new OrderShippedNotification($o, $o->tracking_number),
                'delivered' => new OrderDeliveredNotification($o),
                'cancelled' => new OrderCancelledNotification($o),
                default => null,
            };
            if (! $notif) {
                return;
            }

            /** @var \App\Models\User|null $u */
            $u = $o->user;
            if ($o->user_id && $u) {
                $u->notify($notif);
            }
            if ($o->customer_email && (! $u || $u->email !== $o->customer_email)) {
                Notification::route('mail', $o->customer_email)->notify($notif);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send order status notification: '.$e->getMessage(), ['order_id' => $o->id, 'new_status' => $status]);
        }
    }

    protected function dispatchWebhook(string $event, Order $o): void
    {
        $hooks = Webhook::forEvent($event);
        $payload = [
            'order_id' => $o->id, 'order_number' => $o->order_number, 'status' => $o->order_status,
            'customer_name' => $o->customer_name, 'customer_email' => $o->customer_email,
            'shipping_address' => $o->shipping_address, 'subtotal' => $o->subtotal,
            'discount' => $o->discount_amount, 'total' => $o->total,
            'items_count' => $o->items()->count(), 'created_at' => $o->created_at?->toIso8601String(),
        ];
        foreach ($hooks as $h) {
            DispatchWebhookJob::dispatch($h, $event, $payload);
        }
    }
}
