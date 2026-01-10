<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $trackingNumber = null
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
        $mail = (new MailMessage)
            ->subject(__('notifications.order_shipped_subject', ['order_number' => $this->order->order_number]))
            ->greeting(__('notifications.order_shipped_greeting', ['name' => $this->order->customer_name]))
            ->line(__('notifications.order_shipped_message'))
            ->line(__('notifications.order_number_label', ['order_number' => $this->order->order_number]));

        $trackingNumber = $this->trackingNumber ?? $this->order->tracking_number;
        if ($trackingNumber) {
            $mail->line(__('notifications.tracking_number_label', ['tracking_number' => $trackingNumber]));
        }

        return $mail
            ->action(__('notifications.track_order_action'), route('orders.tracking.show', $this->order->order_number))
            ->line(__('notifications.order_shipped_delivery'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'order_shipped',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'tracking_number' => $this->trackingNumber ?? $this->order->tracking_number,
            'message' => __('notifications.order_shipped_database_message', [
                'order_number' => $this->order->order_number,
            ]),
            'url' => route('orders.tracking.show', $this->order->order_number),
        ];
    }
}
