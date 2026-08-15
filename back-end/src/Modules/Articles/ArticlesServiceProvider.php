<?php

namespace Modules\Articles;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Articles\Models\Post;
use Modules\Articles\Policies\PostPolicy;
use Modules\Articles\Services\PostInfoService;
use Modules\Articles\Services\Contracts\PostInfoContract;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Modules\Articles\Services\PostService;

class ArticlesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PostInfoContract::class, PostInfoService::class);
        $this->app->bind(PostAdminServiceInterface::class, PostService::class);
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
    }
}