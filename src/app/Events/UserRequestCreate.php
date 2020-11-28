<?php

namespace App\Events;

use App\Models\UserRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRequestCreate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userRequest;

    /**
     * Create a new event instance.
     *
     * @param UserRequest $userRequest
     * @param \App\Models\StoreApplication $storeApplication
     * @return void
     */
    public function __construct(UserRequest $userRequest)
    {
        $this->userRequest = $userRequest;
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
