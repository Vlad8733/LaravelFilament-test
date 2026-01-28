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

    public int $tries = 3;

    public Ticket $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable): array
    {

        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.new_ticket_subject', ['subject' => $this->ticket->subject]))
            ->greeting(__('notifications.new_ticket_greeting'))
            ->line(__('notifications.new_ticket_line', [
                'user' => $this->ticket->user->name ?? 'User',
                'subject' => $this->ticket->subject,
            ]))
            ->line(__('notifications.priority').': '.ucfirst($this->ticket->priority))
            ->action(__('notifications.view_ticket'), route('filament.admin.resources.tickets.view', $this->ticket));
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'message' => __('notifications.ticket_created_message', ['subject' => $this->ticket->subject]),
            'priority' => $this->ticket->priority,
            'user_name' => $this->ticket->user->name ?? 'User',
        ];
    }
}
