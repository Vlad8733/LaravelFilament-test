<?php

namespace App\Policies;

use App\Models\CustomerReview;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerReviewPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CustomerReview $review): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'seller' && $review->product) {
            return $review->product->user_id === $user->id;
        }

        return $review->user_id === $user->id || $review->status === 'approved';
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CustomerReview $review): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        return $review->user_id === $user->id && $review->status === 'pending';
    }

    public function delete(User $user, CustomerReview $review): bool
    {

        if ($user->role === 'admin') {
            return true;
        }

        return $review->user_id === $user->id;
    }

    public function moderate(User $user, CustomerReview $review): bool
    {
        return $user->role === 'admin';
    }
}
