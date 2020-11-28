<?php

namespace App\Http\Controllers\Profile\User;

use App\Http\Controllers\DatabaseController;
use App\Models\User;
use Illuminate\Http\Request;

class BaseController extends DatabaseController
{
    protected $profile = 'users.profile.index';

    protected $user = null;

    public function __construct(Request $request, User $userModel)
    {
        $this->profile = view($this->profile);

        $user = $userModel->newQuery()
            ->where('uuid', $request->route('uuid'))
            ->first();

        if ($user !== null) {
            $this->user = $user;
            $this->profile
                ->with('user', $user);
        } else {
            abort(404);
        }
    }
}
