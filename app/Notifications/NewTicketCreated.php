<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
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
        return (new MailMessage)
            ->subject('New Support Ticket #' . $this->ticket->id)
            ->greeting('Hello Admin!')
            ->line('A new support ticket has been created.')
            ->line('**Subject:** ' . $this->ticket->subject)
            ->line('**Priority:** ' . ucfirst($this->ticket->priority))
            ->line('**From:** ' . $this->ticket->user->name . ' (' . $this->ticket->user->email . ')')
            ->action('View Ticket', url('/admin/tickets/' . $this->ticket->id))
            ->line('Please review and respond as soon as possible.');
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
            'ticket_priority' => $this->ticket->priority,
            'user_name' => $this->ticket->user->name,
            'user_email' => $this->ticket->user->email,
            'message' => 'New ticket #' . $this->ticket->id . ' from ' . $this->ticket->user->name,
        ];
    }
}
