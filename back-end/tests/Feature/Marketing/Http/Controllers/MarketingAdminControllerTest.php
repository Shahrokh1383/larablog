<?php

use Shared\Models\User;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\Services\NewsletterService;
use Modules\Marketing\Services\ContactService;
use Modules\Marketing\Actions\SubscribeToNewsletterAction;
use Modules\Marketing\Actions\SendContactEmailAction;
use Modules\Marketing\Jobs\SendBestPostsNewsletterJob;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Queue::fake();

    $this->newsletterService = new NewsletterService(new SubscribeToNewsletterAction());
    $this->contactService = new ContactService(new SendContactEmailAction());
    $this->app->instance(NewsletterService::class, $this->newsletterService);
    $this->app->instance(ContactService::class, $this->contactService);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin, 'sanctum');
});

test('admin can list subscribers', function () {
    Subscriber::factory()->count(3)->create();

    $response = $this->getJson('/api/admin/subscribers');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('admin can send newsletter to all', function () {
    $response = $this->postJson('/api/admin/newsletter/send', [
        'send_to_all' => true,
    ]);

    $response->assertStatus(202)
        ->assertJson(['message' => 'Newsletter dispatch job queued successfully.']);
    Queue::assertPushed(SendBestPostsNewsletterJob::class);
});

test('admin can send newsletter to selected subscribers', function () {
    $subscribers = Subscriber::factory()->count(2)->create();
    $ids = $subscribers->pluck('id')->toArray();

    $response = $this->postJson('/api/admin/newsletter/send', [
        'send_to_all' => false,
        'subscriber_ids' => $ids,
    ]);

    $response->assertStatus(202);
    Queue::assertPushed(SendBestPostsNewsletterJob::class, function ($job) use ($ids) {
        return $job->subscriberIds === $ids;
    });
});

test('admin cannot send newsletter without subscriber selection', function () {
    $response = $this->postJson('/api/admin/newsletter/send', [
        'send_to_all' => false,
        'subscriber_ids' => [],
    ]);

    $response->assertStatus(422);
});

test('admin can delete subscriber', function () {
    $subscriber = Subscriber::factory()->create();
    $response = $this->deleteJson("/api/admin/subscribers/{$subscriber->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('marketing_subscribers', ['id' => $subscriber->id]);
});

test('admin can toggle subscriber status', function () {
    $subscriber = Subscriber::factory()->create(['is_active' => true]);
    $response = $this->patchJson("/api/admin/subscribers/{$subscriber->id}/toggle-status");

    $response->assertStatus(200);
    expect($subscriber->fresh()->is_active)->toBeFalse();
});

test('admin can list contact messages', function () {
    ContactMessage::factory()->count(3)->create();
    $response = $this->getJson('/api/admin/contact-messages');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('admin can view contact message and marks as read', function () {
    $message = ContactMessage::factory()->create(['is_read' => false]);
    $response = $this->getJson("/api/admin/contact-messages/{$message->id}");

    $response->assertStatus(200)
        ->assertJson(['id' => $message->id, 'is_read' => true]);
    $this->assertDatabaseHas('marketing_contact_messages', ['id' => $message->id, 'is_read' => true]);
});

test('admin can delete contact message', function () {
    $message = ContactMessage::factory()->create();
    $response = $this->deleteJson("/api/admin/contact-messages/{$message->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('marketing_contact_messages', ['id' => $message->id]);
});

test('admin can toggle read status', function () {
    $message = ContactMessage::factory()->create(['is_read' => false]);
    $response = $this->patchJson("/api/admin/contact-messages/{$message->id}/toggle-read");

    $response->assertStatus(200);
    expect($message->fresh()->is_read)->toBeTrue();
});

test('admin can reply to contact message', function () {
    $message = ContactMessage::factory()->create(['replied_at' => null]);
    $response = $this->postJson("/api/admin/contact-messages/{$message->id}/reply", [
        'reply_body' => 'Thanks for reaching out',
    ]);

    $response->assertStatus(202);
    Mail::assertQueued(\Modules\Marketing\Mail\AdminReplyMail::class);
    expect($message->fresh()->replied_at)->not->toBeNull();
});

test('non-admin cannot access admin endpoints', function () {
    $user = User::factory()->create(); // no admin role
    $this->actingAs($user, 'sanctum');

    $response = $this->getJson('/api/admin/subscribers');
    $response->assertStatus(403);
});