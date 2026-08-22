<?php

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\TagService;
use Modules\Articles\Services\Contracts\PostAdminServiceInterface;
use Mockery\MockInterface;

it('paginates tags with post counts', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getTotalPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => $ids === [$tag->id])
            ->andReturn([$tag->id => 9]);
    });

    $service = app(TagService::class);
    $paginator = $service->getAll(15, 1);

    expect($paginator->total())->toBe(1)
        ->and($paginator->items()[0]->posts_count)->toBe(9);
});

it('creates a tag with generated slug', function () {
    $this->mock(PostAdminServiceInterface::class);

    $service = app(TagService::class);
    $tag = $service->create('Laravel Tips');

    expect($tag)->toBeInstanceOf(Tag::class)
        ->and($tag->name)->toBe('Laravel Tips')
        ->and($tag->slug)->toBe('laravel-tips');
});

it('regenerates slug when name changes', function () {
    $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => 'old-tag']);

    $this->mock(PostAdminServiceInterface::class);

    $service = app(TagService::class);
    $updated = $service->update($tag, 'New Tag');

    expect($updated->name)->toBe('New Tag')
        ->and($updated->slug)->toBe('new-tag');
});

it('keeps slug when name unchanged', function () {
    $tag = Tag::factory()->create(['name' => 'Same Tag', 'slug' => 'same-tag']);

    $this->mock(PostAdminServiceInterface::class);

    $service = app(TagService::class);
    $updated = $service->update($tag, 'Same Tag');

    expect($updated->slug)->toBe('same-tag');
});

it('returns tag map by ids', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class);

    $service = app(TagService::class);
    $result = $service->getByIds([$tag1->id, $tag2->id]);

    expect($result)->toHaveKeys([$tag1->id, $tag2->id])
        ->and($result[$tag1->id])->toMatchArray([
            'id' => $tag1->id,
            'name' => $tag1->name,
            'slug' => $tag1->slug,
        ]);
});

it('deletes a tag', function () {
    $tag = Tag::factory()->create();

    $this->mock(PostAdminServiceInterface::class);

    $service = app(TagService::class);
    $service->delete($tag);

    expect(Tag::find($tag->id))->toBeNull();
});