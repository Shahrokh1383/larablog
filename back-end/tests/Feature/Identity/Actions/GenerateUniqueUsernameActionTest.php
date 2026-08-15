<?php

use Modules\Identity\Actions\GenerateUniqueUsernameAction;

it('generates a base username from email', function () {
    $action = new GenerateUniqueUsernameAction();

    expect($action->execute('john.doe@example.com'))
        ->toBe('johndoe');
});

it('sanitizes non-alphanumeric characters', function () {
    $action = new GenerateUniqueUsernameAction();

    expect($action->execute('john_doe+tag@example.com'))
        ->toBe('johndoetag');
});

it('adds suffix when attempt is greater than zero', function () {
    $action = new GenerateUniqueUsernameAction();

    expect($action->execute('john@example.com', 3))
        ->toBe('john3');
});

it('falls back to "user" when base is empty', function () {
    $action = new GenerateUniqueUsernameAction();

    expect($action->execute('@example.com'))
        ->toBe('user');
});

it('truncates to 40 characters including suffix', function () {
    $email = str_repeat('a', 50) . '@example.com';
    $action = new GenerateUniqueUsernameAction();

    $base = $action->execute($email, 0);
    expect(strlen($base))->toBeLessThanOrEqual(40);
    expect($base)->toBe(substr(str_repeat('a', 50), 0, 40));

    $withSuffix = $action->execute($email, 10);
    expect(strlen($withSuffix))->toBeLessThanOrEqual(40);
    expect($withSuffix)->toBe(substr(str_repeat('a', 50), 0, 38) . '10');
});