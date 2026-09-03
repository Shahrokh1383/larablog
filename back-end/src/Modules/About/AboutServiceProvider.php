<?php

namespace Modules\About;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\About\Models\SiteSetting;
use Modules\About\Models\TeamMember;
use Modules\About\Policies\SiteSettingPolicy;
use Modules\About\Policies\TeamMemberPolicy;

class AboutServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(SiteSetting::class, SiteSettingPolicy::class);
        Gate::policy(TeamMember::class, TeamMemberPolicy::class);

        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/admin.php');
    }
}