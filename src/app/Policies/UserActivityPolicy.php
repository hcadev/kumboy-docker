<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserActivityPolicy
{
    use HandlesAuthorization;

    public function viewActivities(User $user, UserActivity $userActivity, $user_uuid)
    {
        return $user->uuid === $user_uuid  OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }
}
