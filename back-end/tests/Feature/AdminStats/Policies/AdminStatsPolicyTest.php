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

test('viewAuthorDashboard allows admin, editor, and author', function () {
    expect($this->policy->viewAuthorDashboard($this->admin))->toBeTrue();
    expect($this->policy->viewAuthorDashboard($this->editor))->toBeTrue();
    expect($this->policy->viewAuthorDashboard($this->author))->toBeTrue();
});

test('viewAuthorDashboard denies regular user', function () {
    expect($this->policy->viewAuthorDashboard($this->regularUser))->toBeFalse();
});

test('viewAdminTopCommenters allows admin and editor', function () {
    expect($this->policy->viewAdminTopCommenters($this->admin))->toBeTrue();
    expect($this->policy->viewAdminTopCommenters($this->editor))->toBeTrue();
});

test('viewAdminTopCommenters denies author and regular user', function () {
    expect($this->policy->viewAdminTopCommenters($this->author))->toBeFalse();
    expect($this->policy->viewAdminTopCommenters($this->regularUser))->toBeFalse();
});