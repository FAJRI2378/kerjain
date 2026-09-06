<?php

namespace App\Policies;

use App\Models\BusinessProfile;
use App\Models\User;

class BusinessProfilePolicy
{
    public function update(User $user, BusinessProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}
