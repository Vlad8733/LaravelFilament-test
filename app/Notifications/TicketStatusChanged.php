<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    protected $oldStatus;

    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Ticket $ticket, string $oldStatus, string $newStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $statusText = ucfirst(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage)
            ->subject(__('notifications.ticket_status_subject', ['id' => $this->ticket->id]))
            ->greeting(__('notifications.ticket_status_greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.ticket_status_updated'))
            ->line('**'.__('notifications.ticket_label', ['subject' => $this->ticket->subject]).'**')
            ->line('**'.__('notifications.new_status', ['status' => $statusText]).'**')
            ->action(__('notifications.view_ticket'), route('tickets.show', $this->ticket))
            ->line(__('notifications.thank_you_patience'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => __('notifications.ticket_status_message', ['id' => $this->ticket->id, 'status' => $this->newStatus]),
        ];
    }
}
