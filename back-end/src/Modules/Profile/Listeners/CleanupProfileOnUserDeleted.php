<?php

namespace Modules\Profile\Listeners;

use Modules\Identity\Events\UserDeleted;
use Modules\Profile\Models\Profile;

class CleanupProfileOnUserDeleted
{
    public function handle(UserDeleted $event): void
    {
        Profile::where('user_id', $event->userId)->delete();
    }
}