<?php

namespace Modules\Identity\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class VerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $user,
        public string $verificationUrl
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Verify Email Address')
            ->view('identity::emails.verify-email')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}