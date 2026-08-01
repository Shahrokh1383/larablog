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

class ContentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings needed for now
    }

    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
    }
}