<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketReplied extends Notification
{
    use Queueable;

    public Ticket $ticket;

    public TicketMessage $message;

    public function __construct(Ticket $ticket, TicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $isAdminReply = $this->message->is_admin_reply;

        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'message_id' => $this->message->id,
            'message_preview' => \Str::limit($this->message->message, 100),
            'message' => $isAdminReply
                ? __('notifications.ticket_reply_support', ['subject' => $this->ticket->subject])
                : __('notifications.ticket_reply_user', ['subject' => $this->ticket->subject]),
            'is_admin_reply' => $isAdminReply,
            'sender_name' => $this->message->user->name ?? 'Support',
        ];
    }
}
