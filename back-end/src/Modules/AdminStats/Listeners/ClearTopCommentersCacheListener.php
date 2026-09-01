<?php

namespace Modules\AdminStats\Listeners;

use Illuminate\Support\Facades\Cache;
use Modules\AdminStats\Services\ContentStatsService;
use Modules\Engagement\Events\CommentCreated;

class ClearTopCommentersCacheListener
{
    public function handle(CommentCreated $event): void
    {
        Cache::forget(ContentStatsService::TOP_COMMENTERS_CACHE_KEY);
    }
}