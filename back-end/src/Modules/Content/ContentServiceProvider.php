<?php

namespace Modules\Content;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Content\Models\Post;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Content\Policies\PostPolicy;
use Modules\Content\Policies\CategoryPolicy;
use Modules\Content\Policies\TagPolicy;
use Modules\Content\Services\ContentStatsService;
use Modules\Content\Services\Contracts\ContentStatsContract;
use Modules\Content\Services\PostInfoService;
use Modules\Content\Services\Contracts\PostAdminServiceInterface;
use Modules\Content\Services\PostService;

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ContentStatsContract::class, ContentStatsService::class);
        $this->app->bind(\Modules\Content\Services\Contracts\PostInfoContract::class, PostInfoService::class);
        $this->app->bind(PostAdminServiceInterface::class, PostService::class);
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
    }
}