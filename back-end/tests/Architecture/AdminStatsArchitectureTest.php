<?php

use Modules\AdminStats\Policies\AdminStatsPolicy;
use Shared\Models\User;

beforeEach(function () {
    $this->policy = new AdminStatsPolicy();
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
    $this->editor = User::factory()->create();
    $this->editor->assignRole('editor');
    $this->author = User::factory()->create();
    $this->author->assignRole('author');
    $this->regularUser = User::factory()->create();
    $this->regularUser->assignRole('user');
});

test('viewDashboard allows admin and editor', function () {
    expect($this->policy->viewDashboard($this->admin))->toBeTrue();
    expect($this->policy->viewDashboard($this->editor))->toBeTrue();
});

test('viewDashboard denies author and regular user', function () {
    expect($this->policy->viewDashboard($this->author))->toBeFalse();
    expect($this->policy->viewDashboard($this->regularUser))->toBeFalse();
});

test('viewAuthors allows admin and editor', function () {
    expect($this->policy->viewAuthors($this->admin))->toBeTrue();
    expect($this->policy->viewAuthors($this->editor))->toBeTrue();
});

test('viewAuthors denies author and regular user', function () {
    expect($this->policy->viewAuthors($this->author))->toBeFalse();
    expect($this->policy->viewAuthors($this->regularUser))->toBeFalse();
});