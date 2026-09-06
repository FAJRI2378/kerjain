<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function verify(User $admin, User $user): bool
    {
        return $admin->role === 'admin';
    }

    public function suspend(User $admin, User $user): bool
    {
        return $admin->role === 'admin' && $admin->id !== $user->id;
    }
}
