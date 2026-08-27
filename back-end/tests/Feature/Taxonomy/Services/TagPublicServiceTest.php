<?php

use Modules\Taxonomy\Models\Tag;
use Modules\Taxonomy\Services\TagPublicService;
use Modules\Articles\Services\Contracts\PostStatsServiceInterface;
use Mockery\MockInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

it('returns public tags with post counts', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($tag1, $tag2) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->withArgs(fn ($ids) => count($ids) === 2)
            ->andReturn([
                $tag1->id => 5,
                $tag2->id => 10,
            ]);
    });

    $service = app(TagPublicService::class);
    $paginator = $service->getPublicTags(null, 12);

    expect($paginator->total())->toBe(2);
    $items = $paginator->items();
    expect($items[0]->posts_count)->toBeIn([5, 10]);
});

it('returns public tag meta by slug with posts count', function () {
    $tag = Tag::factory()->create(['slug' => 'known-tag']);

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($tag) {
        $mock->shouldReceive('getPublishedPostCountsByTags')
            ->once()
            ->with([$tag->id])
            ->andReturn([$tag->id => 7]);
    });

    $service = app(TagPublicService::class);
    $result = $service->getTagMetaBySlug('known-tag');

    expect($result['id'])->toBe((string) $tag->id)
        ->and($result['posts_count'])->toBe(7);
});

it('throws ModelNotFoundException for missing tag slug', function () {
    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);

    expect(fn () => $service->getTagMetaBySlug('missing'))->toThrow(ModelNotFoundException::class);
});

it('returns popular tags preserving order', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $stats = [
        ['tag_id' => $tag2->id, 'posts_count' => 20, 'total_views' => 200],
        ['tag_id' => $tag1->id, 'posts_count' => 10, 'total_views' => 100],
    ];

    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) use ($stats) {
        $mock->shouldReceive('getPopularTagStats')
            ->once()
            ->with(5)
            ->andReturn($stats);
    });

    $service = app(TagPublicService::class);
    $result = $service->getPopularTags(5);

    expect($result)->toHaveCount(2)
        ->and($result[0]['id'])->toBe($tag2->id)
        ->and($result[0]['posts_count'])->toBe(20)
        ->and($result[0]['total_views'])->toBe(200)
        ->and($result[1]['id'])->toBe($tag1->id);
});

it('returns tags map by post ids', function () {
    $user = User::factory()->create();
    $postId = (string) Str::uuid();

    DB::table('content_posts')->insert([
        'id'         => $postId,
        'title'      => 'Post for Tag Mapping',
        'slug'       => 'post-for-tag-mapping',
        'body'       => 'Body',
        'user_id'    => $user->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $tag = Tag::factory()->create();

    DB::table('content_post_tag')->insert([
        'post_id' => $postId,
        'tag_id'  => $tag->id,
    ]);

    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);
    $result = $service->getTagsByPostIds([$postId]);

    expect($result)->toHaveKey($postId)
        ->and($result[$postId])->toBeArray()->toHaveCount(1)
        ->and($result[$postId][0])->toMatchArray([
            'id'   => $tag->id,
            'name' => $tag->name,
            'slug' => $tag->slug,
        ]);
});

it('returns empty array for getTagsByPostIds with empty ids', function () {
    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);

    expect($service->getTagsByPostIds([]))->toBe([]);
});

it('returns true for tagIdsExist with empty ids', function () {
    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);

    expect($service->tagIdsExist([]))->toBeTrue();
});

it('returns true when all tag ids exist', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);

    expect($service->tagIdsExist([$tag1->id, $tag2->id]))->toBeTrue();
});

it('returns false when some tag ids missing', function () {
    $tag1 = Tag::factory()->create();

    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);

    expect($service->tagIdsExist([$tag1->id, 'missing-id']))->toBeFalse();
});

it('returns empty array for getPopularTags when no stats', function () {
    $this->mock(PostStatsServiceInterface::class, function (MockInterface $mock) {
        $mock->shouldReceive('getPopularTagStats')
            ->once()
            ->with(5)
            ->andReturn([]);
    });

    $service = app(TagPublicService::class);

    expect($service->getPopularTags(5))->toBe([]);
});

it('applies tag post filter to query', function () {
    $postModel = new class extends Model {
        protected $table = 'content_posts';
    };

    $query = $postModel->newQuery();

    $this->mock(PostStatsServiceInterface::class);

    $service = app(TagPublicService::class);
    $filteredQuery = $service->applyTagPostFilter($query, 'tag-uuid');

    expect($filteredQuery->toSql())
        ->toContain('where exists')
        ->toContain('content_post_tag')
        ->and($filteredQuery->getBindings())
        ->toContain('tag-uuid');
});