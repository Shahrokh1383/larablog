<?php

return [
    'enabled' => [
        \Modules\Content\ContentServiceProvider::class,
        \Modules\Identity\IdentityServiceProvider::class,
        \Modules\Administration\AdministrationServiceProvider::class,
        \Modules\Profile\ProfileServiceProvider::class,
        \Modules\Engagement\EngagementServiceProvider::class,
        \Modules\Notification\NotificationServiceProvider::class,
        \Modules\ReaderExperience\ReaderExperienceServiceProvider::class,
        \Modules\Marketing\MarketingServiceProvider::class,
    ],
];