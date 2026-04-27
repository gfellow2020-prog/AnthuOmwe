<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SystemNotification extends Notification
{
    public function __construct(
        private readonly string  $title,
        private readonly string  $message,
        private readonly string  $type  = 'info',   // info|success|warning|error
        private readonly ?string $link  = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => $this->type,
            'link'    => $this->link,
        ];
    }
}
