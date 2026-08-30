<?php

use Pest\Arch\Contracts\ArchExpectation;

test('Engagement module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\Engagement')
        ->toOnlyUse([
            'Modules\Engagement',
            'Shared',
            'Illuminate',
            'Modules\Articles\Services\Contracts',
            'Modules\Profile\Services\Contracts',
        ])
        ->ignoring([
            'Modules\Engagement\EngagementServiceProvider', // provider allowed to bind
        ]);
});

test('Engagement module does not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\Engagement\Services')
        ->toOnlyUse([
            'Modules\Engagement\Services',
            'Modules\Engagement\Models',
            'Modules\Engagement\DTOs',
            'Modules\Engagement\Events',
            'Modules\Articles\Services\Contracts',
            'Modules\Profile\Services\Contracts',
            'Shared',
            'Illuminate',
        ]);
});

test('Engagement module does not import other module models', function () {
    arch()
        ->expect('Modules\Engagement')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Profile\Models')
        ->not->toUse('Modules\Identity\Models');
});