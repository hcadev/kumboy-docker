<?php

namespace App\Events;

use App\Models\StoreRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreRequestCreate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $storeRequest;

    /**
     * Create a new event instance.
     *
     * @param StoreRequest $storeRequest
     * @return void
     */
    public function __construct(StoreRequest $storeRequest)
    {
        $this->storeRequest = $storeRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
