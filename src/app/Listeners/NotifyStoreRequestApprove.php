<?php

namespace App\Listeners;

use App\Events\StoreRequestApprove;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class NotifyStoreRequestApprove
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
        Notification::send(
            User::query()
                ->where('uuid', $event->storeRequest->user_uuid)
                ->first(),
            new \App\Notifications\StoreRequestApprove($event->storeRequest)
        );

        if ($event->storeRequest->type === 'store transfer') {
            $storeTransfer = $event->storeRequest->storeTransfer()->first();

            Notification::send(
                User::query()
                    ->where('uuid', $storeTransfer->target_uuid)
                    ->first(),
                new \App\Notifications\StoreTransferred($storeTransfer)
            );
        }
    }
}
