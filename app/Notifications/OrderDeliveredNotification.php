<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.order_delivered_subject', ['order_number' => $this->order->order_number]))
            ->greeting(__('notifications.order_delivered_greeting', ['name' => $this->order->customer_name]))
            ->line(__('notifications.order_delivered_message'))
            ->line(__('notifications.order_number_label', ['order_number' => $this->order->order_number]))
            ->line(__('notifications.order_delivered_enjoy'))
            ->action(__('notifications.leave_review_action'), route('orders.tracking.show', $this->order->order_number))
            ->line(__('notifications.order_delivered_thank_you'));
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'order_delivered',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => __('notifications.order_delivered_database_message', [
                'order_number' => $this->order->order_number,
            ]),
            'url' => route('orders.tracking.show', $this->order->order_number),
        ];
    }
}
