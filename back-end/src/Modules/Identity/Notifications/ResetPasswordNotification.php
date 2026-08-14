<?php

namespace Modules\Identity\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Identity\Mail\ResetPasswordMail;
use Modules\Identity\Models\User;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): ResetPasswordMail
    {
        $frontendUrl = config('app.frontend_url');

        $url = "{$frontendUrl}/reset-password?token={$this->token}&email=" . urlencode($notifiable->email);

        return (new ResetPasswordMail($notifiable, $url))
            ->to($notifiable->email);
    }
}