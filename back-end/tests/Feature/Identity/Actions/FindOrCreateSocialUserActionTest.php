<?php

use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Modules\Identity\Actions\CreateUserWithUniqueUsernameAction;
use Modules\Identity\Actions\FindOrCreateSocialUserAction;
use Modules\Identity\Models\SocialAccount;
use Modules\Identity\Models\User;

beforeEach(function () {
    $this->createUser = Mockery::mock(CreateUserWithUniqueUsernameAction::class);
    $this->action = new FindOrCreateSocialUserAction($this->createUser);
});

it('returns existing social account user', function () {
    $user = User::factory()->create();
    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '12345',
    ]);

    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('12345');
    $socialUser->shouldReceive('getEmail')->andReturn($user->email);

    $result = $this->action->execute($socialUser, 'google');

    expect($result->id)->toBe($user->id);
});

it('links new social account to existing user with null password', function () {
    $user = User::factory()->create(['password' => null]);
    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('new-social-id');
    $socialUser->shouldReceive('getEmail')->andReturn($user->email);
    $socialUser->shouldReceive('getName')->andReturn('John Doe');
    $socialUser->shouldReceive('getNickname')->andReturn(null);

    $result = $this->action->execute($socialUser, 'github');

    expect($result->id)->toBe($user->id);
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => 'new-social-id',
    ]);
});

it('throws validation exception when email already registered with password', function () {
    $user = User::factory()->create();
    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('123');
    $socialUser->shouldReceive('getEmail')->andReturn($user->email);

    $this->expectException(ValidationException::class);

    $this->action->execute($socialUser, 'google');
});

it('creates new user when email does not exist', function () {
    $socialUser = Mockery::mock(SocialiteUser::class);
    $socialUser->shouldReceive('getId')->andReturn('brand-new-id');
    $socialUser->shouldReceive('getEmail')->andReturn('new@example.com');
    $socialUser->shouldReceive('getName')->andReturn('New User');
    $socialUser->shouldReceive('getNickname')->andReturn(null);

    $this->createUser->shouldReceive('execute')
        ->once()
        ->withArgs(function ($dto) {
            return $dto->email === 'new@example.com' && $dto->password === null;
        })
        ->andReturnUsing(function ($dto) {
            $user = User::factory()->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => null,
            ]);
            return $user;
        });

    $result = $this->action->execute($socialUser, 'facebook');

    expect($result->email)->toBe('new@example.com');
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $result->id,
        'provider' => 'facebook',
        'provider_id' => 'brand-new-id',
    ]);
    $this->assertNotNull($result->email_verified_at);
});