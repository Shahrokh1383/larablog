<?php

namespace Modules\Identity\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Modules\Identity\Models\User;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Reset Your Password')
            ->view('identity::emails.reset-password')
            ->with([
                'user'     => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}