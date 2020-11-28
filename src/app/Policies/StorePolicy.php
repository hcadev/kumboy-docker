<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    public function viewUserStores(User $user, Store $store, $user_uuid)
    {
        return $user->uuid === $user_uuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function addStore(User $user, Store $store, $user_uuid)
    {
        return $user->uuid === $user_uuid;
    }
}
