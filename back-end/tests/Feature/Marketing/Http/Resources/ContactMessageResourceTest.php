<?php

use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\Http\Resources\ContactMessageResource;
use Shared\Models\User;
use Illuminate\Http\Request;

test('ContactMessageResource with authenticated user', function () {
    $user = User::factory()->create();
    $message = ContactMessage::factory()->forUser($user)->create();

    $resource = new ContactMessageResource($message);
    $array = $resource->toArray(new Request());

    expect($array)->toHaveKeys(['id', 'subject', 'message', 'is_read', 'replied_at', 'author', 'created_at']);
    expect($array['author'])->toBe([
        'id' => $user->id,
        'name' => $user->name,
    ]);
});

test('ContactMessageResource with guest (no user)', function () {
    $message = ContactMessage::factory()->create([
        'name' => 'Guest Name',
        'email' => 'guest@example.com',
        'user_id' => null,
    ]);

    $resource = new ContactMessageResource($message);
    $array = $resource->toArray(new Request());

    expect($array['author'])->toBe([
        'name' => 'Guest Name',
        'email' => 'guest@example.com',
    ]);
});