<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Status Updated - Order #' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->customer_name . '!')
            ->line('Your order status has been updated.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('New Status: ' . $this->order->orderStatus->name)
            ->action('Track Your Order', route('orders.track', $this->order->order_number))
            ->line('Thank you for your order!');
    }
}