<?php

namespace Modules\Identity\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Modules\Identity\Mail\VerificationMail;
use Modules\Identity\Models\User;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): VerificationMail
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new VerificationMail($notifiable, $verificationUrl))
            ->to($notifiable->email);
    }

    protected function verificationUrl(User $notifiable): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}