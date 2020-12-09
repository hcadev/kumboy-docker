<?php

namespace App\Listeners;

use App\Events\StoreRequestApprove;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogStoreRequestApprove
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
     * @param  StoreRequestApprove  $event
     * @return void
     */
    public function handle(StoreRequestApprove $event)
    {
        UserActivity::query()
            ->create([
                'user_uuid' => $event->storeRequest->evaluated_by,
                'date_recorded' => $event->storeRequest->updated_at,
                'action_taken' => 'Approved '
                    .str_replace('_', ' ', $event->storeRequest->type)
                    .' request with reference #'
                    .$event->storeRequest->code.'.',
            ]);
    }
}
