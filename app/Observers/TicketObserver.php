<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Notifications\TicketStatusChanged;

class TicketObserver
{
    public function created(Ticket $t): void {}

    public function updated(Ticket $t): void
    {
        if (! $t->isDirty('status')) {
            return;
        }
        /** @var \App\Models\User $u */
        $u = $t->user;
        $u->notify(new TicketStatusChanged($t, $t->getOriginal('status'), $t->status));
    }

    public function deleted(Ticket $t): void {}

    public function restored(Ticket $t): void {}

    public function forceDeleted(Ticket $t): void {}
}
