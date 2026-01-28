<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'seller') {
            return $order->items()->whereHas('product', fn ($q) => $q->where('user_id', $user->id))->exists();
        }

        return $order->user_id === $user->id || $order->customer_email === $user->email;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }

    public function downloadInvoice(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function requestRefund(User $user, Order $order): bool
    {
        $isOwner = $order->user_id === $user->id || $order->customer_email === $user->email;

        return $isOwner && in_array($order->status->slug, ['delivered', 'completed']);
    }
}
