<?php

namespace App\Notifications;

use App\Models\StoreRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreRequestCreate extends Notification
{
    use Queueable;

    public $storeRequest;

    /**
     * Create a new notification instance.
     *
     * @param StoreRequest $storeRequest
     * @return void
     */
    public function __construct(StoreRequest $storeRequest)
    {
        $this->storeRequest = $storeRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_uuid' => $this->storeRequest->user_uuid,
            'code' => $this->storeRequest->code,
            'type' => 'store_request',
            'message' => $this->storeRequest->status === 'approved'
                            ? 'You have created and approved '.$this->storeRequest->type.' request.'
                            : $this->storeRequest->type.' request has been made.',
        ];
    }
}
