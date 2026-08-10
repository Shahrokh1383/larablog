<?php

namespace Modules\Marketing;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Marketing\Models\Subscriber;
use Modules\Marketing\Models\ContactMessage;
use Modules\Marketing\Policies\MarketingPolicy;

class MarketingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations'); // Assuming flat migrations
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'marketing');

        Gate::policy(Subscriber::class, MarketingPolicy::class);
        Gate::policy(ContactMessage::class, MarketingPolicy::class);
    }
}