<?php

namespace App\Listeners;

use App\Events\UserRequestCreate;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserRequestCreate
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
        UserActivity::query()
            ->create([
                'user_uuid' => $event->userRequest->user_uuid,
                'date_recorded' => $event->userRequest->created_at,
                'action_taken' => 'Created a '
                    .$event->userRequest->type
                    .' request with reference #'
                    .$event->userRequest->code.'.',
            ]);
    }
}
