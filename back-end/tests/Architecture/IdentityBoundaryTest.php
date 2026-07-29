<?php

test('Identity module does not depend on other modules')
    ->expect('Modules\\Identity')
    ->not->toUse('Modules\\Content')
    ->not->toUse('Modules\\Engagement')
    ->not->toUse('Modules\\ReaderExperience')
    ->not->toUse('Modules\\Marketing')
    ->not->toUse('Modules\\About')
    ->not->toUse('Modules\\Administration')
    ->and('Modules\\Identity')
    ->toOnlyUse([
        'App',
        'Illuminate',
        'Spatie',
        'Laravel\\Sanctum',
        'Laravel\\Socialite',
        'Laravel\\Fortify',
        'Modules\\Shared',
    ]);