<?php

namespace App\Policies;

use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RefundRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RefundRequest $refundRequest): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'seller') {
            /** @var \App\Models\Order|null $order */
            $order = $refundRequest->order;
            if ($order) {
                return $order->items()->whereHas('product', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })->exists();
            }
        }

        return $refundRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RefundRequest $refundRequest): bool
    {

        return $user->role === 'admin';
    }

    public function delete(User $user, RefundRequest $refundRequest): bool
    {

        return $user->role === 'admin';
    }

    public function cancel(User $user, RefundRequest $refundRequest): bool
    {

        return $refundRequest->user_id === $user->id && $refundRequest->status === 'pending';
    }
}
