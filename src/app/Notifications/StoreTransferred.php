<?php

namespace App\Notifications;

use App\Models\StoreTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreTransferred extends Notification
{
    use Queueable;

    public $storeTransfer;

    /**
     * Create a new notification instance.
     *
     * @param StoreTransfer $storeTransfer
     * @return void
     */
    public function __construct(StoreTransfer $storeTransfer)
    {
        $this->storeTransfer = $storeTransfer;
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
            'store_uuid' => $this->storeTransfer->uuid,
            'type' => 'store_received',
            'message' => 'A store ownership has been transferred to you.',
        ];
    }
}
