<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public Ticket $ticket;

    public TicketMessage $message;

    public function __construct(Ticket $ticket, TicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isAdminReply = $this->message->is_admin_reply;
        $senderName = $this->message->user->name ?? 'Support';

        return (new MailMessage)
            ->subject(__('notifications.ticket_reply_subject', ['subject' => $this->ticket->subject]))
            ->greeting(__('notifications.ticket_reply_greeting'))
            ->line($isAdminReply
                ? __('notifications.ticket_reply_support_line', ['sender' => $senderName])
                : __('notifications.ticket_reply_user_line', ['sender' => $senderName])
            )
            ->line(Str::limit($this->message->message, 200))
            ->action(__('notifications.view_ticket'), route('tickets.show', $this->ticket));
    }

    public function toArray($notifiable): array
    {
        $isAdminReply = $this->message->is_admin_reply;

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'message_id' => $this->message->id,
            'message_preview' => Str::limit($this->message->message, 100),
            'message' => $isAdminReply
                ? __('notifications.ticket_reply_support', ['subject' => $this->ticket->subject])
                : __('notifications.ticket_reply_user', ['subject' => $this->ticket->subject]),
            'is_admin_reply' => $isAdminReply,
            'sender_name' => $this->message->user->name ?? 'Support',
        ];
    }
}
