<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserRequestPolicy
{
    use HandlesAuthorization;

    public function viewAllRequests(User $user, UserRequest $userRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function viewUserRequests(User $user, UserRequest $userRequest, $user_uuid)
    {
        return $user->uuid === $user_uuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function viewRequestDetails(User $user, UserRequest $userRequest)
    {
        return $user->uuid === $userRequest->user_uuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function cancelRequest(User $user, UserRequest $userRequest)
    {
        return $user->uuid === $userRequest->user_uuid;
    }

    public function approveRequest(User $user, UserRequest $userRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function rejectRequest(User $user, UserRequest $userRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function countPendingRequests(User $user, UserRequest $userRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function addStoreApplication(User $user, UserRequest $userRequest, $user_uuid)
    {
        return $user->uuid === $user_uuid;
    }
}
