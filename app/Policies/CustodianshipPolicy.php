<?php

namespace App\Policies;

use App\Models\Custodianship;
use App\Models\User;

class CustodianshipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Custodianship $custodianship): bool
    {
        return $user->id === $custodianship->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Custodianship $custodianship): bool
    {
        return $user->id === $custodianship->user_id;
    }

    public function delete(User $user, Custodianship $custodianship): bool
    {
        return $user->id === $custodianship->user_id;
    }

    public function restore(User $user, Custodianship $custodianship): bool
    {
        return $user->id === $custodianship->user_id;
    }

    public function forceDelete(User $user, Custodianship $custodianship): bool
    {
        return $user->id === $custodianship->user_id;
    }
}
