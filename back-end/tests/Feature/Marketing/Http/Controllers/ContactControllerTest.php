<?php

use Shared\Models\User;
use Modules\Marketing\Services\ContactService;
use Modules\Marketing\Actions\SendContactEmailAction;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->service = new ContactService(new SendContactEmailAction());
    $this->app->instance(ContactService::class, $this->service);
});

test('store contact message as guest', function () {
    $response = $this->postJson('/api/contact', [
        'name' => 'Guest',
        'email' => 'guest@example.com',
        'subject' => 'Hello',
        'message' => 'Message body',
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Your message has been sent successfully.']);
    $this->assertDatabaseHas('marketing_contact_messages', [
        'name' => 'Guest',
        'email' => 'guest@example.com',
    ]);
});

test('store contact message as authenticated user uses user data', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $response = $this->postJson('/api/contact', [
        'subject' => 'Subject',
        'message' => 'Message',
        // no name/email provided; should come from user
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('marketing_contact_messages', [
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

test('store contact message validation fails if missing required fields', function () {
    $response = $this->postJson('/api/contact', [
        // missing subject and message
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['subject', 'message']);
});