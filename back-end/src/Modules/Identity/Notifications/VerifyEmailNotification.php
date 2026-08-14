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
        $frontendUrl = config('app.frontend_url');

        $params = [
            'id'   => (string) $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            $params
        );

        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $query);

        $frontendQuery = http_build_query(array_merge($params, $query));

        return "{$frontendUrl}/verify-email?{$frontendQuery}";
    }
}