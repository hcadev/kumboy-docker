<?php

namespace App\Listeners;

use App\Events\UserRequestApprove;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyUserRequestApprove
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
     * @param  UserRequestApprove  $event
     * @return void
     */
    public function handle(UserRequestApprove $event)
    {
        Notification::send(
            User::query()
                ->where('uuid', $event->userRequest->user_uuid)
                ->first(),
            new \App\Notifications\UserRequestApprove($event->userRequest)
        );
    }
}
