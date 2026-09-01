<?php

use Pest\Arch\Contracts\ArchExpectation;

test('ReaderExperience module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\ReaderExperience')
        ->toOnlyUse([
            'Modules\ReaderExperience',
            'Shared',
            'Illuminate',
            'Modules\Articles\Services\Contracts',
            'Modules\Engagement\Services\Contracts',
            'Modules\Engagement\Events',
        ])
        ->ignoring([
            'Modules\ReaderExperience\ReaderExperienceServiceProvider', // provider allowed to bind
        ]);
});

test('ReaderExperience Services do not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\ReaderExperience\Services')
        ->toOnlyUse([
            'Modules\ReaderExperience\Services',
            'Modules\ReaderExperience\Actions',
            'Modules\ReaderExperience\Models',
            'Shared',
            'Illuminate',
            'Modules\Articles\Services\Contracts',
            'Modules\Engagement\Services\Contracts',
        ]);
});

test('ReaderExperience module does not import other module models', function () {
    arch()
        ->expect('Modules\ReaderExperience')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Engagement\Models')
        ->not->toUse('Modules\Taxonomy\Models')
        ->not->toUse('Modules\Profile\Models')
        ->not->toUse('Modules\Identity\Models');
});