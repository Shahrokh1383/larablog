<?php

return [
    'enabled' => [
        \Modules\Content\ContentServiceProvider::class,
        \Modules\Identity\IdentityServiceProvider::class,
        \Modules\Administration\AdministrationServiceProvider::class,
        \Modules\Profile\ProfileServiceProvider::class,
    ],
];