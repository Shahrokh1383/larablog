<?php

return [
    'enabled' => [
        \Modules\Taxonomy\TaxonomyServiceProvider::class,
        \Modules\Articles\ArticlesServiceProvider::class,
        \Modules\Home\HomeServiceProvider::class,
        \Modules\Identity\IdentityServiceProvider::class,
        \Modules\Administration\AdministrationServiceProvider::class,
        \Modules\Profile\ProfileServiceProvider::class,
        \Modules\Engagement\EngagementServiceProvider::class,
        \Modules\Notification\NotificationServiceProvider::class,
        \Modules\ReaderExperience\ReaderExperienceServiceProvider::class,
        \Modules\Marketing\MarketingServiceProvider::class,
        \Modules\Search\SearchServiceProvider::class,
        \Modules\About\AboutServiceProvider::class,
    ],
];