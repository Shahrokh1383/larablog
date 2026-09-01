<?php

test('Authors module does not import other modules internal classes', function () {
    arch()
        ->expect('Modules\Authors')
        ->toOnlyUse([
            'Modules\Authors',
            'Shared',
            'Illuminate',
            'Modules\AdminStats\Services\Contracts',
            'Modules\Articles\Services\Contracts',
            'Modules\Profile\Services\Contracts',
        ]);
});

test('Authors module does not import concrete cross-module services', function () {
    arch()
        ->expect('Modules\Authors')
        ->not->toUse('Modules\AdminStats\Services')
        ->not->toUse('Modules\Articles\Services')
        ->not->toUse('Modules\Profile\Services');
});

test('Authors module does not import other module models', function () {
    arch()
        ->expect('Modules\Authors')
        ->not->toUse('Modules\AdminStats\Models')
        ->not->toUse('Modules\Articles\Models')
        ->not->toUse('Modules\Profile\Models');
});