<?php

use Modules\Marketing\Actions\SendContactEmailAction;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->action = new SendContactEmailAction();
});

test('creates contact message and queues email', function () {
    $dto = new ContactMessageDTO(
        name: 'John Doe',
        email: 'john@example.com',
        subject: 'Hello',
        message: 'This is a test message.',
        userId: null
    );

    $message = $this->action->execute($dto);

    expect($message)->toBeInstanceOf(ContactMessage::class);
    $this->assertDatabaseHas('marketing_contact_messages', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Hello',
        'message' => 'This is a test message.',
        'is_read' => false,
    ]);

    Mail::assertQueued(ContactFormMail::class, function ($mail) use ($message) {
        return $mail->contactMessage->id === $message->id;
    });
});

test('creates message with user id if provided', function () {
    $user = \Shared\Models\User::factory()->create();
    $dto = new ContactMessageDTO(
        name: $user->name,
        email: $user->email,
        subject: 'Subject',
        message: 'Body',
        userId: $user->id
    );

    $message = $this->action->execute($dto);

    expect($message->user_id)->toBe($user->id);
});