<?php

namespace Modules\Marketing\Mail;

use Modules\Marketing\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class AdminReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $originalMessage, 
        public string $replyBody
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Re: ' . $this->originalMessage->subject,
            from: new Address(
                config('mail.from.address', 'support@larablog.com'), 
                config('mail.from.name', 'LaraBlog Support')
            ),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'marketing::emails.admin-reply');
    }
}