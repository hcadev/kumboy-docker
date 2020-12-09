<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StoreRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreRequestPolicy
{
    use HandlesAuthorization;

    public function viewAllRequests(User $user, StoreRequest $storeRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function viewStoreRequests(User $user, StoreRequest $storeRequest, $user_uuid)
    {
        return $user->uuid === $user_uuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function viewRequestDetails(User $user, StoreRequest $storeRequest)
    {
        return $user->uuid === $storeRequest->user_uuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function cancelRequest(User $user, StoreRequest $storeRequest)
    {
        return $user->uuid === $storeRequest->user_uuid;
    }

    public function approveRequest(User $user, StoreRequest $storeRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function rejectRequest(User $user, StoreRequest $storeRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function countPendingRequests(User $user, StoreRequest $storeRequest)
    {
        return in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function addStoreApplication(User $user, StoreRequest $storeRequest, $user_uuid)
    {
        return $user->uuid === $user_uuid;
    }
}
