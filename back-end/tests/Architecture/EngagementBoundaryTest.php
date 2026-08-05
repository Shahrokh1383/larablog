<?php

test('engagement module does not import content models')
    ->expect('Modules\Engagement')
    ->not->toUse('Modules\Content\Models');

test('content module does not import engagement models')
    ->expect('Modules\Content')
    ->not->toUse('Modules\Engagement\Models');