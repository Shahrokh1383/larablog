<?php

namespace Modules\Identity\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Models\User;

class RevokeUserSessionsAction
{
    /**
     * Revoke all web sessions for the given user.
     * Centralized to avoid duplicated raw queries and to ease future driver abstraction.
     */
    public function execute(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}