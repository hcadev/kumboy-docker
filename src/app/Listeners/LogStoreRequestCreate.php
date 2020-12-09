<?php

namespace App\Listeners;

use App\Events\StoreRequestCreate;
use App\Models\UserActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogStoreRequestCreate
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
     * @param  StoreRequestCreate  $event
     * @return void
     */
    public function handle(StoreRequestCreate $event)
    {
        UserActivity::query()
            ->create([
                'user_uuid' => $event->storeRequest->user_uuid,
                'date_recorded' => $event->storeRequest->created_at,
                'action_taken' => 'Created '
                    .($event->storeRequest->status === 'approved' ? ' and approved ' : '')
                    .$event->storeRequest->type
                    .' request with reference #'
                    .$event->storeRequest->code.'.',
            ]);
    }
}
