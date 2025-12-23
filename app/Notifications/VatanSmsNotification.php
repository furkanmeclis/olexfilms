<?php

namespace App\Notifications;

use App\Notifications\Channels\VatanSmsChannel;
use Illuminate\Notifications\Notification;

class VatanSmsNotification extends Notification
{
    protected string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return [VatanSmsChannel::class];
    }

    public function toSms($notifiable): string
    {
        return $this->message;
    }
}
