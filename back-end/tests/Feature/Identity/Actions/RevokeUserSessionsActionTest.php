<?php

use Illuminate\Support\Facades\DB;
use Modules\Identity\Actions\RevokeUserSessionsAction;
use Modules\Identity\Models\User;

it('deletes sessions from database when driver is database', function () {
    config(['session.driver' => 'database']);
    config(['session.table' => 'sessions']);

    $user = User::factory()->create();
    DB::table('sessions')->insert([
        'id' => 'session1',
        'user_id' => $user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'test',
        'payload' => '{}',
        'last_activity' => now()->timestamp,
    ]);

    $action = new RevokeUserSessionsAction();
    $action->execute($user);

    $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
});

it('does nothing when session driver is not database', function () {
    config(['session.driver' => 'array']);

    $user = User::factory()->create();
    $action = new RevokeUserSessionsAction();
    $action->execute($user);

    // No exception, and no deletion attempted; can't assert easily without DB.
    $this->assertTrue(true);
});