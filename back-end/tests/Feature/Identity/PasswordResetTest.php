<?php

use Tests\Support\SmtpSinkService;
use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! smtpReachable()) {
        $this->markTestSkipped('SMTP sink server is not running. Skipping email-related tests.');
    }
    $this->smtp = app(SmtpSinkService::class);
    $this->smtp->purgeAll();
});

it('sends a password reset link email', function () {
    $user = User::factory()->create(['email' => 'reset-me@larablog.test']);

    $response = postJson('/api/forgot-password', [
        'email' => 'reset-me@larablog.test',
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __(Password::RESET_LINK_SENT)]);

    sleep(1);

    $received = $this->smtp->findEmailForRecipient('reset-me@larablog.test');
    expect($received)->not->toBeNull();
    expect($received['subject'])->toBe('Reset Your Password');

    $links = $received['links'];
    expect($links)->toBeArray()->not->toBeEmpty();

    $resetLink = collect($links)->firstWhere('text', 'Reset Password');
    expect($resetLink)->not->toBeNull();
    expect($resetLink['url'])->toContain('reset-password');
});

it('resets password with a valid token from email', function () {
    $user = User::factory()->create(['email' => 'valid-reset@larablog.test']);

    postJson('/api/forgot-password', ['email' => 'valid-reset@larablog.test']);
    sleep(1);

    $received = $this->smtp->findEmailForRecipient('valid-reset@larablog.test');
    $resetLinkUrl = collect($received['links'])->firstWhere('text', 'Reset Password')['url'];

    $query = parse_url($resetLinkUrl, PHP_URL_QUERY);
    parse_str($query, $params);
    $token = $params['token'];
    $email = $params['email'];

    $newPassword = 'NewStrongPass999!';
    $response = postJson('/api/reset-password', [
        'token'                 => $token,
        'email'                 => $email,
        'password'              => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => __(Password::PASSWORD_RESET)]);

    $loginResponse = postJson('/api/login', [
        'email'    => 'valid-reset@larablog.test',
        'password' => $newPassword,
    ]);

    $loginResponse->assertStatus(200)
        ->assertJsonStructure(['user', 'token']);
});

it('rejects invalid token', function () {
    postJson('/api/reset-password', [
        'token'                 => 'fake-token',
        'email'                 => 'someone@larablog.test',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertStatus(422)
      ->assertJsonValidationErrors('email');
});