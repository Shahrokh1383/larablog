<?php

use Pest\Arch\Contracts\ArchExpectation;

test('AdminStats module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\AdminStats')
        ->toOnlyUse([
            'Modules\AdminStats',
            'Shared',
            'Illuminate',
            'Modules\Articles\Services\Contracts',
            'Modules\Taxonomy\Services\Contracts',
            'Modules\Engagement\Services\Contracts',
            'Modules\Engagement\Events',
        ])
        ->ignoring([
            'Modules\AdminStats\AdminStatsServiceProvider', // provider allowed to bind
        ]);
});

test('AdminStats Services do not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\AdminStats\Services')
        ->toOnlyUse([
            'Modules\AdminStats\Services',
            'Modules\AdminStats\Policies',
            'Shared',
            'Illuminate',
            'Modules\Articles\Services\Contracts',
            'Modules\Taxonomy\Services\Contracts',
            'Modules\Engagement\Services\Contracts',
            'Modules\Engagement\Events',
        ]);
});

test('AdminStats module does not import other module models', function () {
    arch()
        ->expect('Modules\AdminStats')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Taxonomy\Models')
        ->not->toUse('Modules\Engagement\Models');
});