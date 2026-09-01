<?php

use Modules\Identity\Events\UserDeleted;
use Modules\Profile\Listeners\CleanupProfileOnUserDeleted;
use Modules\Profile\Models\Profile;
use Shared\Models\User;

test('deletes the profile for the given user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $profile1 = Profile::factory()->create(['user_id' => $user1->id]);
    $profile2 = Profile::factory()->create(['user_id' => $user2->id]);

    $listener = new CleanupProfileOnUserDeleted();
    $listener->handle(new UserDeleted($user1->id));

    $this->assertDatabaseMissing('profiles', ['id' => $profile1->id]);
    $this->assertDatabaseHas('profiles', ['id' => $profile2->id]);
});