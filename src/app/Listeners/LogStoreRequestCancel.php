<?php

namespace App\Listeners;

use App\Events\StoreRequestCancel;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogStoreRequestCancel
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
     * @param  StoreRequestCancel  $event
     * @return void
     */
    public function handle(StoreRequestCancel $event)
    {
        UserActivity::query()
            ->create([
                'user_uuid' => $event->storeRequest->user_uuid,
                'date_recorded' => $event->storeRequest->updated_at,
                'action_taken' => 'Cancelled '
                    .str_replace('_', ' ', $event->storeRequest->type)
                    .' request with reference #'
                    .$event->storeRequest->code.'.',
            ]);
    }
}
