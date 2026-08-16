<?php

test('engagement module does not import articles models')
    ->expect('Modules\Engagement')
    ->not->toUse('Modules\Articles\Models');

test('articles module does not import engagement models')
    ->expect('Modules\Articles')
    ->not->toUse('Modules\Engagement\Models');