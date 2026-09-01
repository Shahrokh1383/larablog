<?php

test('Profile module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\Profile')
        ->toOnlyUse([
            'Modules\Profile',
            'Shared',
            'Illuminate',
            'Modules\Identity\Services\Contracts',
            'Modules\Identity\Events',
        ])
        ->ignoring([
            'Modules\Profile\ProfileServiceProvider', // provider allowed to bind
        ]);
});

test('Profile module does not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\Profile\Services')
        ->toOnlyUse([
            'Modules\Profile\Services',
            'Modules\Profile\Models',
            'Modules\Profile\Actions',
            'Modules\Profile\DTOs',
            'Modules\Profile\Events',
            'Modules\Identity\Services\Contracts',
            'Shared',
            'Illuminate',
        ]);
});

test('Profile module does not import other module models', function () {
    arch()
        ->expect('Modules\Profile')
        ->not->toUse('Modules\Identity\Models')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Engagement\Models');
});