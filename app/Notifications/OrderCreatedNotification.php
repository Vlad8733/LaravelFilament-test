<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    /**
     * @param  mixed  $notifiable
     * @return array<string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.order_created_subject', ['order_number' => $this->order->order_number]))
            ->greeting(__('notifications.order_created_greeting', ['name' => $this->order->customer_name]))
            ->line(__('notifications.order_created_thank_you'))
            ->line(__('notifications.order_number_label', ['order_number' => $this->order->order_number]))
            ->line(__('notifications.order_total_label', ['total' => '$'.number_format($this->order->total, 2)]))
            ->action(__('notifications.view_order_action'), route('orders.tracking.show', $this->order->order_number))
            ->line(__('notifications.order_created_processing'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'message' => __('notifications.order_created_message', [
                'order_number' => $this->order->order_number,
            ]),
            'url' => route('orders.tracking.show', $this->order->order_number),
        ];
    }
}
