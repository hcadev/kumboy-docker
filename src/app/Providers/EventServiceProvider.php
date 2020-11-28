<?php

namespace App\Providers;

use App\Events\ApproveStoreApplication;
use App\Events\CancelStoreApplication;
use App\Events\RejectStoreApplication;
use App\Events\UserRequestCreate;
use App\Events\UserAddAddress;
use App\Events\UserChangeName;
use App\Events\UserChangePassword;
use App\Events\UserDeleteAddress;
use App\Events\UserEditAddress;
use App\Events\UserLogin;
use App\Events\UserLogout;
use App\Events\UserRequestApprove;
use App\Events\UserRequestCancel;
use App\Events\UserRequestReject;
use App\Listeners\LogApproveStoreApplication;
use App\Listeners\LogCancelStoreApplication;
use App\Listeners\LogRejectStoreApplication;
use App\Listeners\LogUserRequestCreate;
use App\Listeners\LogUserAddAddress;
use App\Listeners\LogUserChangeName;
use App\Listeners\LogUserChangePassword;
use App\Listeners\LogUserDeleteAddress;
use App\Listeners\LogUserEditAddress;
use App\Listeners\LogUserLogin;
use App\Listeners\LogUserLogout;
use App\Listeners\LogUserRequestApprove;
use App\Listeners\LogUserRequestCancel;
use App\Listeners\LogUserRequestReject;
use App\Listeners\NotifyUserRequestApprove;
use App\Listeners\NotifyUserRequestCreate;
use App\Listeners\NotifyUserRequestReject;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        UserLogin::class => [
            LogUserLogin::class,
        ],
        UserLogout::class => [
            LogUserLogout::class,
        ],
        UserChangeName::class => [
            LogUserChangeName::class,
        ],
        UserChangePassword::class => [
            LogUserChangePassword::class,
        ],
        UserAddAddress::class => [
            LogUserAddAddress::class,
        ],
        UserEditAddress::class => [
            LogUserEditAddress::class,
        ],
        UserDeleteAddress::class => [
            LogUserDeleteAddress::class,
        ],
        UserRequestCancel::class => [
            LogUserRequestCancel::class,
        ],
        UserRequestApprove::class => [
            LogUserRequestApprove::class,
            NotifyUserRequestApprove::class,
        ],
        UserRequestReject::class => [
            LogUserRequestReject::class,
            NotifyUserRequestReject::class,
        ],
        UserRequestCreate::class => [
            LogUserRequestCreate::class,
            NotifyUserRequestCreate::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
