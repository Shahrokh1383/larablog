<?php

use Modules\Taxonomy\Models\Category;
use Modules\Taxonomy\Services\CategoryService;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Mockery\MockInterface;

it('paginates categories with post counts', function () {
    $category = Category::factory()->create();

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 7]);
    });

    $service = app(CategoryService::class);
    $paginator = $service->getAll(15, 1);

    expect($paginator->total())->toBe(1)
        ->and($paginator->items()[0]->posts_count)->toBe(7);
});

it('creates a category with generated slug', function () {
    $this->mock(PostAdminServiceInterface::class);

    $service = app(CategoryService::class);
    $category = $service->create('Hello World');

    expect($category)->toBeInstanceOf(Category::class)
        ->and($category->name)->toBe('Hello World')
        ->and($category->slug)->toBe('hello-world');
});

it('regenerates slug when name changes', function () {
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 0]);
    });

    $service = app(CategoryService::class);
    $updated = $service->update($category, 'New Name');

    expect($updated->name)->toBe('New Name')
        ->and($updated->slug)->toBe('new-name');
});

it('keeps slug when name unchanged', function () {
    $category = Category::factory()->create(['name' => 'Same Name', 'slug' => 'same-name']);

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($category) {
        $mock->shouldReceive('getTotalPostCountsByCategories')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$category->id])
            ->andReturn([$category->id => 0]);
    });

    $service = app(CategoryService::class);
    $updated = $service->update($category, 'Same Name');

    expect($updated->slug)->toBe('same-name');
});

it('returns category map by ids', function () {
    $cat1 = Category::factory()->create();
    $cat2 = Category::factory()->create();

    $this->mock(PostAdminServiceInterface::class);

    $service = app(CategoryService::class);
    $result = $service->getByIds([$cat1->id, $cat2->id]);

    expect($result)->toHaveKeys([$cat1->id, $cat2->id])
        ->and($result[$cat1->id])->toMatchArray([
            'id' => $cat1->id,
            'name' => $cat1->name,
            'slug' => $cat1->slug,
        ]);
});

it('deletes a category', function () {
    $category = Category::factory()->create();

    $this->mock(PostAdminServiceInterface::class);

    $service = app(CategoryService::class);
    $service->delete($category);

    expect(Category::find($category->id))->toBeNull();
});