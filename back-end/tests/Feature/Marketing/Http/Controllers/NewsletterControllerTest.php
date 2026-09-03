<?php

use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\DTOs\SubscribeDTO;
use Modules\Marketing\Services\NewsletterService;
use Modules\Marketing\Actions\SubscribeToNewsletterAction;

beforeEach(function () {
    $this->service = new NewsletterService(new SubscribeToNewsletterAction());
    $this->app->instance(NewsletterService::class, $this->service);
});

test('subscribe success', function () {
    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'subscriber@example.com',
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Successfully subscribed to the newsletter.']);
    $this->assertDatabaseHas('marketing_subscribers', ['email' => 'subscriber@example.com']);
});

test('subscribe validation fails for invalid email', function () {
    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'not-an-email',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('subscribe throws 422 if already subscribed', function () {
    Subscriber::factory()->create(['email' => 'existing@example.com', 'is_active' => true]);
    $response = $this->postJson('/api/newsletter/subscribe', [
        'email' => 'existing@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Already subscribed']);
});