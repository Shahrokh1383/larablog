<?php

namespace Modules\Taxonomy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Policies\CategoryPolicy;
use Modules\Taxonomy\Policies\TagPolicy;
use Modules\Taxonomy\Services\CategoryPublicService;
use Modules\Taxonomy\Services\TagPublicService;
use Modules\Taxonomy\Services\Contracts\CategoryPublicServiceInterface;
use Modules\Taxonomy\Services\Contracts\TagPublicServiceInterface;

class TaxonomyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CategoryPublicServiceInterface::class,
            CategoryPublicService::class
        );

        $this->app->bind(
            TagPublicServiceInterface::class,
            TagPublicService::class
        );
    }

    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
    }
}