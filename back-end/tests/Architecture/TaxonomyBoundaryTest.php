<?php

test('Taxonomy module does not depend on other modules internal classes')
    ->expect('Modules\Taxonomy')
    ->not->toUse('Modules\Articles\Models')
    ->not->toUse('Modules\Articles\Services\PostPublicService')
    ->not->toUse('Modules\Articles\Services\PostService')
    ->not->toUse('Modules\Articles\Http')
    ->not->toUse('Modules\Identity')
    ->not->toUse('Modules\Profile')
    ->not->toUse('Modules\Engagement')
    ->not->toUse('Modules\ReaderExperience')
    ->not->toUse('Modules\Marketing')
    ->not->toUse('Modules\About')
    ->not->toUse('Modules\AdminStats')
    ->not->toUse('Modules\Home')
    ->not->toUse('Modules\Notification')
    ->not->toUse('Modules\Search')
    ->and('Modules\Taxonomy')
    ->toOnlyUse([
        'App',
        'Illuminate',
        'Shared',
        'Modules\Articles\Services\Contracts',
    ]);