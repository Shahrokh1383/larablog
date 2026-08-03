<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Content\Models\Category;
use Modules\Content\Models\Tag;
use Modules\Content\Models\Post;
use Modules\Identity\Models\User;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 200 categories and tags
        $categories = Category::factory()->count(200)->create();
        $tags = Tag::factory()->count(200)->create();

        // Get all user IDs (they were already seeded)
        $userIds = User::pluck('id')->all();

        // Create 200 posts with random relationships
        Post::factory()
            ->count(200)
            ->make()
            ->each(function (Post $post) use ($userIds, $categories, $tags) {
                // Random user
                $post->user_id = $userIds[array_rand($userIds)];

                // Random category (nullable, 80% chance of having a category)
                $post->category_id = rand(1, 10) > 2
                    ? $categories->random()->id
                    : null;

                // Publish 70% of posts
                $post->is_published = rand(1, 10) > 3;
                if ($post->is_published) {
                    $post->published_at = now()->subDays(rand(0, 365));
                } else {
                    $post->published_at = null;
                }

                // Random meta
                $post->views = rand(0, 5000);
                $post->reading_time = rand(1, 15);

                // Generate excerpt from body if null
                if (empty($post->excerpt)) {
                    $post->excerpt = substr($post->body, 0, 150);
                }

                $post->save();

                // Attach 1–4 random tags
                $post->tags()->attach(
                    $tags->random(rand(1, 4))->pluck('id')->all()
                );
            });
    }
}