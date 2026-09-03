<?php

test('About module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\About')
        ->toOnlyUse([
            'Modules\About',
            'Shared',
            'Illuminate',
            'Modules\Identity\Services\Contracts',
            'Modules\Profile\Services\Contracts',
        ])
        ->ignoring([
            'Modules\About\AboutServiceProvider', // provider allowed to bind
        ]);
});

test('About Services do not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\About\Services')
        ->toOnlyUse([
            'Modules\About\Services',
            'Modules\About\Actions',
            'Modules\About\DTOs',
            'Modules\About\Models',
            'Shared',
            'Illuminate',
            'Modules\Identity\Services\Contracts',
            'Modules\Profile\Services\Contracts',
        ]);
});

test('About module does not import other module models', function () {
    arch()
        ->expect('Modules\About')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Taxonomy\Models')
        ->not->toUse('Modules\Engagement\Models')
        ->not->toUse('Modules\Marketing\Models')
        ->not->toUse('Modules\AdminStats\Models');
});