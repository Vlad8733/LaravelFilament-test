<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public ?string $reason = null
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
            ->subject(__('notifications.order_cancelled_subject', ['order_number' => $this->order->order_number]))
            ->greeting(__('notifications.order_cancelled_greeting', ['name' => $this->order->customer_name]))
            ->line(__('notifications.order_cancelled_message'))
            ->line(__('notifications.order_number_label', ['order_number' => $this->order->order_number]));

        if ($this->reason) {
            $mail->line(__('notifications.order_cancelled_reason', ['reason' => $this->reason]));
        }

        return $mail
            ->line(__('notifications.order_cancelled_refund_info'))
            ->action(__('notifications.contact_support_action'), route('tickets.create'))
            ->line(__('notifications.order_cancelled_apologies'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => 'order_cancelled',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'reason' => $this->reason,
            'message' => __('notifications.order_cancelled_database_message', [
                'order_number' => $this->order->order_number,
            ]),
            'url' => route('orders.tracking.show', $this->order->order_number),
        ];
    }
}
