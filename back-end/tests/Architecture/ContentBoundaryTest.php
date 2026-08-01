<?php

test('Content module does not import other modules models')
    ->expect('Modules\\Content')
    ->not->toUse('Modules\\Identity\\Models')
    ->not->toUse('Modules\\Engagement\\Models')
    ->not->toUse('Modules\\ReaderExperience\\Models')
    ->and('Modules\\Content')
    ->not->toUse('Modules\\Marketing\\Models')
    ->not->toUse('Modules\\About\\Models')
    ->not->toUse('Modules\\Administration\\Models');

test('Content module may only use Shared kernel classes')
    ->expect('Modules\\Content')
    ->toUse('Shared\\Models\\User')
    ->toUse('Shared\\Traits\\HasUuid')
    ->toUse('Shared\\ValueObjects\\Slug')
    ->toUse('Shared\\Contracts\\HasRolesContract');