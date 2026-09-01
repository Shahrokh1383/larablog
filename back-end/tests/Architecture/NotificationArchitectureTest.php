<?php

test('Notification module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\Notification')
        ->toOnlyUse([
            'Modules\Notification',
            'Shared',
            'Illuminate',
        ])
        ->ignoring([
            'Modules\Notification\NotificationServiceProvider', // provider allowed to bind
        ]);
});

test('Notification module does not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\Notification\Services')
        ->toOnlyUse([
            'Modules\Notification\Services',
            'Shared',
            'Illuminate',
        ]);
});

test('Notification module does not import other module models', function () {
    arch()
        ->expect('Modules\Notification')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Engagement\Models')
        ->not->toUse('Modules\Taxonomy\Models')
        ->not->toUse('Modules\Profile\Models')
        ->not->toUse('Modules\Identity\Models');
});