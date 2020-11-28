<?php

namespace App\Notifications;

use App\Models\UserRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRequestApprove extends Notification
{
    use Queueable;

    public $userRequest;

    /**
     * Create a new notification instance.
     *
     * @param UserRequest $userRequest
     * @return void
     */
    public function __construct(UserRequest $userRequest)
    {
        $this->userRequest = $userRequest;
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
            'date_recorded' => $this->userRequest->updated_at,
            'user_uuid' => $this->userRequest->user_uuid,
            'reference_number' => $this->userRequest->code,
            'type' => $this->userRequest->type,
            'status' => $this->userRequest->status,
        ];
    }
}
