<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAddressBook;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserAddressBookPolicy
{
    use HandlesAuthorization;

    public function viewAddressBook(User $user, UserAddressBook $userAddressBook, $userUuid)
    {
        return $user->uuid === $userUuid OR in_array(strtolower($user->role), ['superadmin', 'admin']);
    }

    public function addAddress(User $user, UserAddressBook $userAddressBook, $userUuid)
    {
        return $user->uuid === $userUuid;
    }

    public function editAddress(User $user, UserAddressBook $userAddressBook)
    {
        return $user->uuid === $userAddressBook->user_uuid;
    }

    public function deleteAddress(User $user, UserAddressBook $userAddressBook)
    {
        return $user->uuid === $userAddressBook->user_uuid;
    }
}
