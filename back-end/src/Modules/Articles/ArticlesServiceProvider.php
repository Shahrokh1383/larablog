<?php

namespace Modules\Articles;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Articles\Models\Post;
use Modules\Articles\Policies\PostPolicy;
use Modules\Articles\Services\PostInfoService;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Articles\Services\Contracts\PostAdminStatsServiceInterface;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Modules\Articles\Services\PostService;
use Modules\Articles\Services\PostPublicService;
use Modules\Articles\Services\PostAdminStatsService;
use Modules\Articles\Services\PostStatsService;

class ArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostInfoContract::class, PostInfoService::class);
        $this->app->bind(PostAdminServiceInterface::class, PostService::class);
        $this->app->bind(PostAdminStatsServiceInterface::class, PostAdminStatsService::class);
        $this->app->bind(PostPublicServiceInterface::class, PostPublicService::class);
        $this->app->bind(PostStatsServiceInterface::class, PostStatsService::class);
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
    }
}