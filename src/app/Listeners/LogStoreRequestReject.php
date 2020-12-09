<?php

namespace App\Listeners;

use App\Events\StoreRequestReject;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogStoreRequestReject
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
     * @param  StoreRequestReject  $event
     * @return void
     */
    public function handle(StoreRequestReject $event)
    {
        UserActivity::query()
            ->create([
                'user_uuid' => $event->storeRequest->evaluated_by,
                'date_recorded' => $event->storeRequest->updated_at,
                'action_taken' => 'Rejected '
                    .str_replace('_', ' ', $event->storeRequest->type)
                    .' request with reference #'
                    .$event->storeRequest->code.'.',
            ]);
    }
}
