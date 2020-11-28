<?php

namespace App\Listeners;

use App\Events\UserRequestCancel;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserRequestCancel
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
     * @param  UserRequestCancel  $event
     * @return void
     */
    public function handle(UserRequestCancel $event)
    {
        UserActivity::query()
            ->create([
                'user_uuid' => $event->request->user_uuid,
                'date_recorded' => now(),
                'action_taken' => 'Cancelled '
                    .$event->request->type
                    .' request with reference #'
                    .$event->request->code.'.',
            ]);
    }
}
