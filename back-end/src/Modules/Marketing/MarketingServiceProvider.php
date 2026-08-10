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
        // Load module-specific views for Mailables
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'marketing');

        // Register Policies
        Gate::policy(Subscriber::class, MarketingPolicy::class);
        Gate::policy(ContactMessage::class, MarketingPolicy::class);
    }
}