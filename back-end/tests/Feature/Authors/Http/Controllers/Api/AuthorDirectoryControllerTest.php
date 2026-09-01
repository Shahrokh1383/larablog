<?php

use Modules\AdminStats\Services\Contracts\ContentStatsContract;
use Modules\Articles\Models\Post;
use Modules\Articles\Services\Contracts\PostPublicServiceInterface;
use Modules\Profile\Models\Profile;
use Modules\Profile\Services\Contracts\FetchesPublicProfiles;
use Shared\Models\User;

beforeEach(function () {
    // Mock cross-module services
    $this->mockProfileService = Mockery::mock(FetchesPublicProfiles::class);
    $this->mockContentStatsService = Mockery::mock(ContentStatsContract::class);
    $this->mockPostService = Mockery::mock(PostPublicServiceInterface::class);

    $this->app->instance(FetchesPublicProfiles::class, $this->mockProfileService);
    $this->app->instance(ContentStatsContract::class, $this->mockContentStatsService);
    $this->app->instance(PostPublicServiceInterface::class, $this->mockPostService);
});

test('index returns paginated author directory with stats', function () {
    // Create two profiles with users
    $user1 = User::factory()->create(['name' => 'Alice']);
    $user2 = User::factory()->create(['name' => 'Bob']);
    $profile1 = Profile::factory()->create(['user_id' => $user1->id, 'bio' => 'Bio Alice', 'expertise' => 'PHP']);
    $profile2 = Profile::factory()->create(['user_id' => $user2->id, 'bio' => 'Bio Bob', 'expertise' => 'JS']);

    // Build a LengthAwarePaginator with these profiles (with user relation loaded)
    $profilesPaginator = Profile::with('user')
        ->whereIn('id', [$profile1->id, $profile2->id])
        ->paginate(12);

    $this->mockProfileService
        ->shouldReceive('getAllPublicProfiles')
        ->once()
        ->with(null, 12)
        ->andReturn($profilesPaginator);

    $stats = [
        $user1->id => ['posts_count' => 5, 'total_views' => 100],
        $user2->id => ['posts_count' => 3, 'total_views' => 50],
    ];

    $this->mockContentStatsService
        ->shouldReceive('getAuthorStatsForUserIds')
        ->once()
        ->with(Mockery::on(function ($ids) use ($user1, $user2) {
            return in_array($user1->id, $ids, true) && in_array($user2->id, $ids, true);
        }))
        ->andReturn($stats);

    $response = $this->getJson('/api/authors');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'username', 'avatar', 'bio',
                    'expertise', 'posts_count', 'total_views',
                ],
            ],
            'links', 'meta',
        ]);

    // Assert stats are attached
    $responseData = $response->json('data');
    $alice = collect($responseData)->firstWhere('id', $user1->id);
    $bob = collect($responseData)->firstWhere('id', $user2->id);

    expect($alice['posts_count'])->toBe(5);
    expect($alice['total_views'])->toBe(100);
    expect($bob['posts_count'])->toBe(3);
    expect($bob['total_views'])->toBe(50);
});

test('index respects search and per_page query parameters', function () {
    $user = User::factory()->create(['name' => 'Searchable']);
    $profile = Profile::factory()->create(['user_id' => $user->id, 'bio' => 'Bio']);

    $profilesPaginator = Profile::with('user')->where('id', $profile->id)->paginate(5);

    $this->mockProfileService
        ->shouldReceive('getAllPublicProfiles')
        ->once()
        ->with('searchTerm', 5)
        ->andReturn($profilesPaginator);

    $this->mockContentStatsService
        ->shouldReceive('getAuthorStatsForUserIds')
        ->once()
        ->andReturn([$user->id => ['posts_count' => 0, 'total_views' => 0]]);

    $this->getJson('/api/authors?search=searchTerm&per_page=5')
        ->assertOk();
});

test('show returns author profile with stats', function () {
    $user = User::factory()->create(['username' => 'johndoe', 'name' => 'John Doe']);
    
    Profile::factory()->create([
        'user_id' => $user->id,
        'avatar' => 'http://example.com/avatar.png',
        'bio' => 'About John',
        'expertise' => 'Laravel',
        'years_of_experience' => 10,
        'social_links' => ['https://twitter.com/johndoe'],
    ]);

    // CRITICAL FIX: Re-fetch the profile from the database.
    // This resets Eloquent's internal `wasRecentlyCreated` flag to false.
    // If we pass the factory-created instance directly to the mock, Laravel's 
    // Resource will see `wasRecentlyCreated = true` and incorrectly return a 201 status.
    $profile = Profile::with('user')->where('user_id', $user->id)->first();

    $this->mockProfileService
        ->shouldReceive('getPublicProfileByUsername')
        ->once()
        ->with('johndoe')
        ->andReturn($profile);

    $this->mockContentStatsService
        ->shouldReceive('getAuthorStatsForUserId')
        ->once()
        ->with($user->id)
        ->andReturn(['posts_count' => 7, 'total_views' => 150]);

    $response = $this->getJson('/api/authors/johndoe');

    $response->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'John Doe')
        ->assertJsonPath('data.username', 'johndoe')
        ->assertJsonPath('data.posts_count', 7)
        ->assertJsonPath('data.total_views', 150)
        ->assertJsonPath('data.years_of_experience', 10);
});

test('show returns 404 when profile not found', function () {
    $this->mockProfileService
        ->shouldReceive('getPublicProfileByUsername')
        ->once()
        ->with('unknown')
        ->andReturnNull();

    $this->getJson('/api/authors/unknown')
        ->assertStatus(404);
});

test('posts returns paginated posts by author username', function () {
    $user = User::factory()->create(['username' => 'author']);
    $posts = Post::factory()->count(3)->create(['user_id' => $user->id, 'is_published' => true]);
    $postsPaginator = Post::where('user_id', $user->id)->paginate(6);

    $this->mockPostService
        ->shouldReceive('getPostsByAuthor')
        ->once()
        ->with('author', 'newest', 6)
        ->andReturn($postsPaginator);

    $response = $this->getJson('/api/authors/author/posts');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'slug', 'title', 'excerpt', 'featured_image',
                    'published_at', 'views', 'reading_time',
                ],
            ],
            'links', 'meta',
        ]);

    $this->assertCount(3, $response->json('data'));
});

test('posts respects sort and per_page query parameters', function () {
    $user = User::factory()->create(['username' => 'author2']);
    $posts = Post::factory()->count(2)->create(['user_id' => $user->id]);
    $postsPaginator = Post::where('user_id', $user->id)->paginate(3);

    $this->mockPostService
        ->shouldReceive('getPostsByAuthor')
        ->once()
        ->with('author2', 'oldest', 3)
        ->andReturn($postsPaginator);

    $this->getJson('/api/authors/author2/posts?sort=oldest&per_page=3')
        ->assertOk();
});