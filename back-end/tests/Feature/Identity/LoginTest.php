<?php

use Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('logs in with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('Password123!')]);

    $response = postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'Password123!',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'token']);
});

it('rejects invalid credentials', function () {
    $response = postJson('/api/login', [
        'email'    => 'ghost@larablog.test',
        'password' => 'wrong',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('email');
});