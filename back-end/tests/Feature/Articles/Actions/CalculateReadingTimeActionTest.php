<?php

use Modules\Articles\Actions\CalculateReadingTimeAction;

it('calculates reading time for plain text', function () {
    $body = 'word '.str_repeat('word ', 399); // 400 words

    $minutes = app(CalculateReadingTimeAction::class)->execute($body);

    expect($minutes)->toBe(2);
});

it('returns minimum one minute for short text', function () {
    $minutes = app(CalculateReadingTimeAction::class)->execute('short');

    expect($minutes)->toBe(1);
});

it('strips html before counting words', function () {
    $body = '<p>word '.str_repeat('word ', 399).'</p>';

    $minutes = app(CalculateReadingTimeAction::class)->execute($body);

    expect($minutes)->toBe(2);
});