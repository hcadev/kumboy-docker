<?php

namespace App\Listeners;

use App\Events\UserRequestApprove;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserRequestApprove
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
        UserActivity::query()
            ->create([
                'user_uuid' => $event->userRequest->evaluated_by,
                'date_recorded' => $event->userRequest->updated_at,
                'action_taken' => 'Approved '
                    .$event->userRequest->type
                    .' request with reference #'
                    .$event->userRequest->code.'.',
            ]);
    }
}
