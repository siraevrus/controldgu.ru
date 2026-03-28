<?php

namespace App\Policies;

use App\Models\Dgu;
use App\Models\User;

class DguPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dgu $dgu): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Dgu $dgu): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Dgu $dgu): bool
    {
        return $user->hasRole('admin');
    }

    public function controlOperational(User $user, Dgu $dgu): bool
    {
        return $user->hasAnyRole(['admin', 'operator']);
    }

    public function restore(User $user, Dgu $dgu): bool
    {
        return false;
    }

    public function forceDelete(User $user, Dgu $dgu): bool
    {
        return false;
    }
}
