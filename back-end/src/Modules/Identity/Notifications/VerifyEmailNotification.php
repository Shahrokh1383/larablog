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
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        
        $params = [
            'id'   => (string) $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        // Generate the backend signed URL to get expires and signature
        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            $params
        );

        // Parse the backend URL to extract the query parameters (expires, signature)
        parse_str(parse_url($signedUrl, PHP_URL_QUERY), $query);

        // Merge id, hash, expires, and signature for the frontend URL
        $frontendQuery = http_build_query(array_merge($params, $query));

        return "{$frontendUrl}/verify-email?{$frontendQuery}";
    }
}