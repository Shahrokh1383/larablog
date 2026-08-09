<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Identity\Models\User;

class ContentSeeder extends Seeder
{
    private const POSTS_COUNT = 1_000;
    private const CATEGORIES_COUNT = 50;
    private const TAGS_COUNT = 500;
    private const TAGS_PER_POST_MIN = 5;
    private const TAGS_PER_POST_MAX = 15;
    private const POST_CHUNK_SIZE = 500;
    private const PIVOT_CHUNK_SIZE = 2000;

    public function run(): void
    {
        $this->command?->info('Seeding categories...');
        $categories = Category::factory()->count(self::CATEGORIES_COUNT)->create();

        $this->command?->info('Seeding tags...');
        $tags = Tag::factory()->count(self::TAGS_COUNT)->create();

        $this->command?->info('Fetching author/editor user IDs...');
        // Only users with author or editor role can be post authors
        $userIds = User::role(['author', 'editor'])->pluck('id')->all();

        if (empty($userIds)) {
            $this->command?->error('No author/editor users found. Run UserSeeder first.');
            return;
        }

        $this->command?->info('Generating post data...');
        $posts = $this->generatePostData($userIds, $categories);

        $this->command?->info('Inserting posts in chunks...');
        $this->insertPosts($posts);

        $this->command?->info('Generating tag associations...');
        $pivotRecords = $this->generateTagAssociations($tags);

        $this->command?->info('Inserting tag associations in chunks...');
        $this->insertPivotRecords($pivotRecords);

        $this->command?->info('Content seeding completed.');
    }

    private function generatePostData(array $userIds, $categories): array
    {
        $posts = [];
        $now = now()->toDateTimeString();
        $categoryIds = $categories->pluck('id')->all();

        for ($i = 0; $i < self::POSTS_COUNT; $i++) {
            $title = fake()->sentence();
            $isPublished = rand(1, 10) > 3; // 70% chance published

            $posts[] = [
                'id'             => (string) Str::orderedUuid(),
                'title'          => $title,
                'slug'           => Str::slug($title) . '-' . Str::random(6),
                'body'           => fake()->paragraphs(rand(2, 5), true),
                'excerpt'        => null,
                'featured_image' => null,
                'is_published'   => $isPublished,
                'published_at'   => $isPublished
                    ? fake()->dateTimeBetween('-1 year')->format('Y-m-d H:i:s')
                    : null,
                'reading_time'   => rand(1, 15),
                'views'          => rand(0, 5000),
                'user_id'        => $userIds[array_rand($userIds)],
                'category_id'    => rand(1, 10) > 2
                    ? $categoryIds[array_rand($categoryIds)]
                    : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        return $posts;
    }

    private function insertPosts(array $posts): void
    {
        foreach (array_chunk($posts, self::POST_CHUNK_SIZE) as $chunk) {
            DB::table('content_posts')->insert($chunk);
        }
    }

    private function generateTagAssociations($tags): array
    {
        $postIds = DB::table('content_posts')->pluck('id')->all();
        $tagIds = $tags->pluck('id')->all();
        $pivots = [];

        foreach ($postIds as $postId) {
            $count = rand(self::TAGS_PER_POST_MIN, self::TAGS_PER_POST_MAX);
            // Pick random unique tags
            $selectedTags = (array) array_rand(array_flip($tagIds), $count);
            foreach ($selectedTags as $tagId) {
                $pivots[] = [
                    'post_id' => $postId,
                    'tag_id'  => $tagId,
                ];
            }
        }

        return $pivots;
    }

    private function insertPivotRecords(array $records): void
    {
        foreach (array_chunk($records, self::PIVOT_CHUNK_SIZE) as $chunk) {
            DB::table('content_post_tag')->insert($chunk);
        }
    }
}