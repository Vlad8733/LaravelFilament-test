<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {

        return $user !== null;
    }

    public function view(?User $user, Product $product): bool
    {
        if (! $user) {
            return $product->is_active;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSeller()) {
            return $product->user_id === $user->id || $product->is_active;
        }

        return $product->is_active;
    }

    public function create(User $user): bool
    {
        return $user->isSeller() || $user->isAdmin();
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $product->user_id === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $product->user_id === $user->id;
    }
}
