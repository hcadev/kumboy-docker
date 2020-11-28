<?php

namespace App\Listeners;

use App\Events\UserRequestCreate;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUserRequestCreate
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRequestCreate  $event
     * @return void
     */
    public function handle(UserRequestCreate $event)
    {
        Notification::send(
            User::query()
                ->whereRaw('role REGEXP "admin"')
                ->whereNull('banned_until')
                ->get(),
            new \App\Notifications\UserRequestCreate($event->userRequest)
        );
    }
}
