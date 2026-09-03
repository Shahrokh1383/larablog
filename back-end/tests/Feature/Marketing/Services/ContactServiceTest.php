<?php

use Modules\Marketing\Services\ContactService;
use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\DTOs\ContactMessageDTO;
use Modules\Marketing\Exceptions\MarketingException;
use Modules\Marketing\Mail\AdminReplyMail;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->service = new ContactService(
        new \Modules\Marketing\Actions\SendContactEmailAction()
    );
});

test('submitMessage creates message and queues email', function () {
    $dto = new ContactMessageDTO(
        name: 'Jane',
        email: 'jane@example.com',
        subject: 'Subject',
        message: 'Body',
        userId: null
    );

    $message = $this->service->submitMessage($dto);
    expect($message)->toBeInstanceOf(ContactMessage::class);
    $this->assertDatabaseHas('marketing_contact_messages', ['email' => 'jane@example.com']);
    Mail::assertQueued(\Modules\Marketing\Mail\ContactFormMail::class);
});

test('getAdminMessages returns paginated messages with user relation', function () {
    ContactMessage::factory()->count(5)->create();
    $paginator = $this->service->getAdminMessages(perPage: 2);
    expect($paginator)->toHaveCount(2);
    expect($paginator->total())->toBe(5);
    expect($paginator->first()->relationLoaded('user'))->toBeTrue();
});

test('markAsRead updates is_read to true', function () {
    $message = ContactMessage::factory()->create(['is_read' => false]);
    $this->service->markAsRead($message);
    expect($message->fresh()->is_read)->toBeTrue();
});

test('markAsRead does not update if already read', function () {
    $message = ContactMessage::factory()->read()->create();
    $original = $message->updated_at;
    $this->service->markAsRead($message);
    expect($message->fresh()->updated_at->eq($original))->toBeTrue();
});

test('deleteMessage deletes the message', function () {
    $message = ContactMessage::factory()->create();
    $this->service->deleteMessage($message);
    $this->assertDatabaseMissing('marketing_contact_messages', ['id' => $message->id]);
});

test('replyToMessage marks replied_at and queues admin reply', function () {
    $message = ContactMessage::factory()->create(['replied_at' => null]);
    $replyBody = 'Thank you for your message.';

    $this->service->replyToMessage($message, $replyBody);

    $message->refresh();
    expect($message->replied_at)->not->toBeNull();
    expect($message->is_read)->toBeTrue(); // markAsRead called
    Mail::assertQueued(AdminReplyMail::class, function ($mail) use ($message, $replyBody) {
        return $mail->originalMessage->id === $message->id && $mail->replyBody === $replyBody;
    });
});

test('replyToMessage throws alreadyReplied if already replied', function () {
    $message = ContactMessage::factory()->create(['replied_at' => now()]);
    expect(fn () => $this->service->replyToMessage($message, 'Second reply'))
        ->toThrow(MarketingException::class, 'This contact message has already been replied to.');
});

test('replyToMessage rolls back replied_at if email fails', function () {
    Mail::shouldReceive('to->queue')->andThrow(new \Exception('Mail error'));
    $message = ContactMessage::factory()->create(['replied_at' => null]);

    try {
        $this->service->replyToMessage($message, 'Reply');
    } catch (\Exception $e) {
        // expected
    }

    expect($message->fresh()->replied_at)->toBeNull();
});

test('toggleReadStatus toggles is_read', function () {
    $message = ContactMessage::factory()->create(['is_read' => false]);
    $this->service->toggleReadStatus($message);
    expect($message->fresh()->is_read)->toBeTrue();

    $this->service->toggleReadStatus($message);
    expect($message->fresh()->is_read)->toBeFalse();
});