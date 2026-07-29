<?php

use Tests\Support\SmtpSinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Purge SMTP inbox before each test
    $this->smtp = app(SmtpSinkService::class);
    $this->smtp->purgeAll();
});

it('registers a user and sends verification email', function () {
    $email = 'jane@larablog.test';

    $response = postJson('/api/register', [
        'name'                  => 'Jane Doe',
        'email'                 => $email,
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token']);

    // Wait for email to arrive in SMTP sink
    sleep(1);

    $received = $this->smtp->findEmailForRecipient($email);
    expect($received)->not->toBeNull();
    expect($received['subject'])->toBe('Verify Email Address');

    $links = $received['links'];
    expect($links)->toBeArray()->not->toBeEmpty();

    $verifyLink = collect($links)->firstWhere('text', 'Verify Email Address');
    expect($verifyLink)->not->toBeNull();
    expect($verifyLink['url'])->toContain('verify-email');
});

it('prevents duplicate email registration', function () {
    // Create a user first
    \Modules\Identity\Models\User::factory()->create(['email' => 'dup@larablog.test']);

    $response = postJson('/api/register', [
        'name'                  => 'Duplicate',
        'email'                 => 'dup@larablog.test',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});