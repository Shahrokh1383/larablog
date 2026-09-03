<?php

test('Marketing module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\Marketing')
        ->toOnlyUse([
            'Modules\Marketing',
            'Shared',
            'Illuminate',
        ])
        ->ignoring([
            'Modules\Marketing\MarketingServiceProvider', // provider allowed to bind
        ]);
});

test('Marketing Services do not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\Marketing\Services')
        ->toOnlyUse([
            'Modules\Marketing\Services',
            'Modules\Marketing\Actions',
            'Modules\Marketing\DTOs',
            'Modules\Marketing\Models',
            'Modules\Marketing\Exceptions',
            'Shared',
            'Illuminate',
        ]);
});

test('Marketing module does not import other module models', function () {
    arch()
        ->expect('Modules\Marketing')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Taxonomy\Models')
        ->not->toUse('Modules\Engagement\Models')
        // Add any other module namespaces that should not be used.
        ->not->toUse('Modules\AdminStats\Models');
});