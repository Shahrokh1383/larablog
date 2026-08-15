<?php

namespace Modules\Content;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Content\Policies\CategoryPolicy;
use Modules\Content\Policies\TagPolicy;
use Modules\Content\Services\ContentStatsService;
use Modules\Content\Services\Contracts\ContentStatsContract;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContentStatsContract::class, ContentStatsService::class);
    }

    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
    }
}