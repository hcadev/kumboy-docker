<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAllUsers(User $userAuth, User $userModel)
    {
        return in_array(strtolower($userAuth->role), ['superadmin', 'admin']);
    }

    public function viewAccountSettings(User $userAuth, User $userModel)
    {
        return $userAuth->id === $userModel->id;
    }

    public function changeName(User $userAuth, User $userModel)
    {
        return $userAuth->id === $userModel->id;
    }

    public function changePassword(User $userAuth, User $userModel)
    {
        return $userAuth->id === $userModel->id;
    }
}
