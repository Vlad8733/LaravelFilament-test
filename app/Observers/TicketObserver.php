<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusChanged;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "updated" event.
     */
    public function updated(Ticket $ticket): void
    {
        // Проверяем, изменился ли статус
        if ($ticket->isDirty('status')) {
            $oldStatus = $ticket->getOriginal('status');
            $newStatus = $ticket->status;

            // Отправляем уведомление пользователю
            /** @var User $user */
            $user = $ticket->user;
            $user->notify(new TicketStatusChanged($ticket, $oldStatus, $newStatus));
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        //
    }

    /**
     * Handle the Ticket "force deleted" event.
     */
    public function forceDeleted(Ticket $ticket): void
    {
        //
    }
}
