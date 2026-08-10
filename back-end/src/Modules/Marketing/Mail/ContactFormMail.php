<?php

namespace Modules\Marketing\Mail;

use Modules\Marketing\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message: ' . $this->contactMessage->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'marketing::emails.contact-form',
        );
    }
}