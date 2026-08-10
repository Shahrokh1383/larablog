<?php

namespace Modules\Marketing\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BestPostsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $posts) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Larablog: Top Posts of the Week',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'marketing::emails.best-posts',
        );
    }
}