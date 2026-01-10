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
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        Log::info('OrderObserver: Order created', ['order_id' => $order->id, 'order_number' => $order->order_number]);
        $this->dispatchWebhook(Webhook::EVENT_ORDER_CREATED, $order);
        $this->sendOrderCreatedNotification($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Check if status changed
        if ($order->wasChanged('order_status')) {
            $webhooks = Webhook::forEvent(Webhook::EVENT_ORDER_STATUS_CHANGED);

            $payload = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $order->getOriginal('order_status'),
                'new_status' => $order->order_status,
                'customer_email' => $order->customer_email,
                'total' => $order->total,
            ];

            foreach ($webhooks as $webhook) {
                DispatchWebhookJob::dispatch($webhook, Webhook::EVENT_ORDER_STATUS_CHANGED, $payload);
            }

            // Send status-specific email notifications
            $this->sendStatusChangeNotification($order);
        }
    }

    /**
     * Send notification when order is created.
     */
    protected function sendOrderCreatedNotification(Order $order): void
    {
        try {
            // Notify registered user
            /** @var \App\Models\User|null $user */
            $user = $order->user;
            if ($order->user_id && $user) {
                $user->notify(new OrderCreatedNotification($order));
            }

            // Also send email to customer_email (for guest orders or different email)
            if ($order->customer_email && (! $user || $user->email !== $order->customer_email)) {
                Notification::route('mail', $order->customer_email)
                    ->notify(new OrderCreatedNotification($order));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send order created notification: '.$e->getMessage(), [
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Send notification based on new order status.
     */
    protected function sendStatusChangeNotification(Order $order): void
    {
        $newStatus = $order->order_status;

        try {
            $notification = match ($newStatus) {
                'shipped' => new OrderShippedNotification($order, $order->tracking_number),
                'delivered' => new OrderDeliveredNotification($order),
                'cancelled' => new OrderCancelledNotification($order),
                default => null,
            };

            if ($notification) {
                // Notify registered user
                /** @var \App\Models\User|null $user */
                $user = $order->user;
                if ($order->user_id && $user) {
                    $user->notify($notification);
                }

                // Also send email to customer_email (for guest orders or different email)
                if ($order->customer_email && (! $user || $user->email !== $order->customer_email)) {
                    Notification::route('mail', $order->customer_email)
                        ->notify($notification);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send order status notification: '.$e->getMessage(), [
                'order_id' => $order->id,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Dispatch webhook for order.
     */
    protected function dispatchWebhook(string $event, Order $order): void
    {
        $webhooks = Webhook::forEvent($event);

        $payload = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->order_status,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'shipping_address' => $order->shipping_address,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'total' => $order->total,
            'items_count' => $order->items()->count(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];

        foreach ($webhooks as $webhook) {
            DispatchWebhookJob::dispatch($webhook, $event, $payload);
        }
    }
}
