<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;
use Modules\Identity\Actions\RevokeUserSessionsAction;
use Modules\Identity\Events\UserDeleted;
use Modules\Identity\Events\UserNameUpdated;
use Modules\Identity\Events\UserPasswordUpdated;
use Modules\Identity\Events\UserRoleUpdated;
use Modules\Identity\Models\User;
use Modules\Identity\Services\UserService;

beforeEach(function () {
    $this->revokeSessions = Mockery::mock(RevokeUserSessionsAction::class);
    $this->service = new UserService($this->revokeSessions);
});

it('returns paginated users', function () {
    User::factory()->count(20)->create();

    $result = $this->service->getAllUsers(10);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($result->total())->toBe(20);
    expect($result->perPage())->toBe(10);
});

it('filters users by search', function () {
    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $result = $this->service->getAllUsers(15, 'John');

    expect($result->total())->toBe(1);
    expect($result->first()->name)->toBe('John Doe');
});

it('updates user role and dispatches event', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    Event::fake();

    $updated = $this->service->updateRole($user, 'editor');

    Event::assertDispatched(UserRoleUpdated::class, fn ($e) => $e->user->id === $user->id && $e->role === 'editor');
    expect($updated->hasRole('editor'))->toBeTrue();
});

it('updates user password, revokes sessions, and dispatches event', function () {
    $user = User::factory()->create();
    $this->revokeSessions->shouldReceive('execute')->once()->with($user);

    Event::fake();

    $updated = $this->service->updatePassword($user, 'new-password');

    Event::assertDispatched(UserPasswordUpdated::class);
    expect(Hash::check('new-password', $updated->password))->toBeTrue();
});

it('updates user name and dispatches event', function () {
    $user = User::factory()->create();

    Event::fake();

    $this->service->updateName($user, 'New Name');

    Event::assertDispatched(UserNameUpdated::class);
    expect($user->fresh()->name)->toBe('New Name');
});

it('deletes account, revokes sessions, and dispatches event', function () {
    $user = User::factory()->create();
    $this->revokeSessions->shouldReceive('execute')
        ->once()
        ->with(\Mockery::on(function ($u) use ($user) {
            return $u instanceof User && $u->id === $user->id;
        }));

    Event::fake();

    $this->service->deleteAccount($user->id);

    Event::assertDispatched(UserDeleted::class, fn ($e) => $e->userId === $user->id);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('returns paginated users with roles', function () {
    $admin1 = User::factory()->create();
    $admin1->assignRole('admin');
    $admin2 = User::factory()->create();
    $admin2->assignRole('admin');
    $user = User::factory()->create();
    $user->assignRole('user');

    $result = $this->service->getPaginatedUsersWithRoles(['admin'], null, 10);

    expect($result->total())->toBe(2);
    expect($result->first()['roles'])->toContain('admin');
});

it('returns users with roles map', function () {
    $user1 = User::factory()->create();
    $user1->assignRole('user');
    $user2 = User::factory()->create();
    $user2->assignRole('editor');

    $map = $this->service->getUsersWithRolesMap([$user1->id, $user2->id]);

    expect($map)->toHaveCount(2);
    expect($map[$user1->id]['roles'])->toContain('user');
    expect($map[$user2->id]['roles'])->toContain('editor');
});

it('returns empty map for empty ids', function () {
    expect($this->service->getUsersWithRolesMap([]))->toBe([]);
});