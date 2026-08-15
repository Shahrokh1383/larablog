<?php

use Modules\Identity\Http\Resources\UserResource;
use Modules\Identity\Models\User;

it('formats user resource with roles when loaded', function () {
    $user = User::factory()->create();
    $user->assignRole('user');
    $user->load('roles');

    $resource = new UserResource($user);
    $data = $resource->toArray(request());

    expect($data)->toHaveKeys(['id', 'name', 'email', 'username', 'roles', 'created_at']);
    expect($data['roles'])->toBe(['user']);
});