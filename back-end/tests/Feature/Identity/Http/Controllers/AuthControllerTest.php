<?php

use Illuminate\Support\Facades\Auth;
use Modules\Identity\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['password' => 'password']);
});

it('registers a user', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user' => ['id', 'name', 'email', 'username', 'roles']]);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
});

it('logs in a user', function () {
    $response = $this->postJson('/api/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonStructure(['user']);
    $this->assertAuthenticatedAs($this->user);
});

it('logs in admin and returns token', function () {
    $admin = User::factory()->create(['password' => 'password']);
    $admin->assignRole('admin');

    $response = $this->postJson('/api/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['user', 'token']);
});

it('fails admin login for non-admin', function () {
    $response = $this->postJson('/api/admin/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(403);
});

it('returns authenticated user', function () {
    $this->actingAs($this->user, 'sanctum');

    $response = $this->getJson('/api/user');

    $response->assertOk()->assertJsonPath('user.id', $this->user->id);
});

it('logs out user', function () {
    $this->actingAs($this->user, 'sanctum');
    $token = $this->user->createToken('test')->plainTextToken;
    $this->user->withAccessToken($this->user->tokens()->first());

    $response = $this->postJson('/api/logout');

    $response->assertOk();
    expect($this->user->tokens()->count())->toBe(0);
});

it('verifies email with signed route', function () {
    $user = User::factory()->unverified()->create();
    $hash = sha1($user->getEmailForVerification());
    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => $hash,
    ]);

    $response = $this->getJson($url);

    $response->assertOk()->assertJsonPath('message', 'Email verified successfully');
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});