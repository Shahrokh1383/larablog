<?php

namespace Modules\Marketing\Actions;

use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

class SendContactEmailAction
{
    public function execute(ContactMessageDTO $dto): ContactMessage
    {
        $message = ContactMessage::create([
            'user_id' => $dto->userId,
            'name'    => $dto->name,
            'email'   => $dto->email,
            'subject' => $dto->subject,
            'message' => $dto->message,
        ]);

        // Send email to admin (queued for performance)
        Mail::to(config('mail.admin_address', 'LaraBlog@gmail.com'))
            ->queue(new ContactFormMail($message));

        return $message;
    }
}