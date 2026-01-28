<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {

        if ($user->role === 'admin' || $ticket->assigned_to === $user->id) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Ticket $ticket): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        return $ticket->user_id === $user->id && $ticket->status !== 'closed';
    }

    public function reply(User $user, Ticket $ticket): bool
    {

        if ($ticket->status === 'closed') {
            return false;
        }

        if ($user->role === 'admin' || $ticket->assigned_to === $user->id) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {

        return $user->role === 'admin';
    }

    public function close(User $user, Ticket $ticket): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        if ($ticket->assigned_to === $user->id) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }
}
