<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Company $company): bool
    {
        if ($company->is_active || $user->role === 'admin') {
            return true;
        }

        return $company->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['seller', 'admin']);
    }

    public function update(User $user, Company $company): bool
    {
        return $user->role === 'admin' || $company->user_id === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->role === 'admin' || $company->user_id === $user->id;
    }

    public function verify(User $user, Company $company): bool
    {
        return $user->role === 'admin';
    }
}
