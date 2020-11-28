<?php

namespace App\Listeners;

use App\Events\UserRequestReject;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUserRequestReject
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
     * @param  UserRequestReject  $event
     * @return void
     */
    public function handle(UserRequestReject $event)
    {
        Notification::send(
            User::query()
                ->where('uuid', $event->userRequest->user_uuid)
                ->first(),
            new \App\Notifications\UserRequestReject($event->userRequest)
        );
    }
}
