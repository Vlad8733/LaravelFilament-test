<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, TicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $isAdminReply = $this->message->is_admin_reply;

        return (new MailMessage)
            ->subject('New Reply on Ticket #' . $this->ticket->id)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($isAdminReply ? 'Support team has replied to your ticket.' : 'New reply added to your ticket.')
            ->line('**Ticket:** ' . $this->ticket->subject)
            ->line('**Reply from:** ' . $this->message->user->name)
            ->line('**Message:**')
            ->line('"' . \Str::limit($this->message->message, 200) . '"')
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Thank you for contacting our support team!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'message_id' => $this->message->id,
            'reply_from' => $this->message->user->name,
            'is_admin_reply' => $this->message->is_admin_reply,
            'message' => 'New reply on ticket #' . $this->ticket->id,
        ];
    }
}
