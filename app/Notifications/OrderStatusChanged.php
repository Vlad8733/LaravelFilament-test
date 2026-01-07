<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.order_status_subject', ['order_number' => $this->order->order_number]))
            ->greeting(__('notifications.order_status_greeting', ['name' => $this->order->customer_name]))
            ->line(__('notifications.order_status_updated'))
            ->line(__('notifications.order_number_label', ['order_number' => $this->order->order_number]))
            ->line(__('notifications.new_status_label', ['status' => $this->order->orderStatus->name ?? 'Updated']))
            ->action(__('notifications.track_order_action'), route('orders.tracking.show', $this->order->order_number))
            ->line(__('notifications.thank_you_order'));
    }

    public function toArray($notifiable): array
    {
        $statusName = $this->order->orderStatus?->name ?? 'Updated';
        
        return [
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $statusName,
            'message' => __('notifications.order_status_message', [
                'order_number' => $this->order->order_number,
                'status' => $statusName,
            ]),
            'url' => route('orders.tracking.show', $this->order->order_number),
        ];
    }
}
