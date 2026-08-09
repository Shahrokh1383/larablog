<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class CommentSeeder extends Seeder
{
    private const CHUNK_SIZE = 1000;
    private const TOP_LEVEL_COMMENTS_MIN = 10;
    private const TOP_LEVEL_COMMENTS_MAX = 30;
    private const REPLIES_PER_COMMENT_MIN = 5;
    private const REPLIES_PER_COMMENT_MAX = 15;

    public function run(): void
    {
        $this->command?->info('Seeding comments and replies for all published posts...');

        $postIds = DB::table('content_posts')->where('is_published', true)->pluck('id')->all();
        $userIds = User::pluck('id')->all();

        if (empty($postIds) || empty($userIds)) {
            $this->command?->error('No published posts or users found. Run ContentSeeder and UserSeeder first.');
            return;
        }

        $commentsChunk = [];
        $totalInserted = 0;

        foreach ($postIds as $postId) {
            $topLevelCount = rand(self::TOP_LEVEL_COMMENTS_MIN, self::TOP_LEVEL_COMMENTS_MAX);

            for ($i = 0; $i < $topLevelCount; $i++) {
                $topLevelId = (string) Str::orderedUuid();
                $hasUser = rand(1, 10) > 2; // 80% registered users, 20% guests
                $createdAt = fake()->dateTimeBetween('-6 months')->format('Y-m-d H:i:s');

                // Add Top-Level Comment
                $commentsChunk[] = [
                    'id'          => $topLevelId,
                    'post_id'     => $postId,
                    'user_id'     => $hasUser ? $userIds[array_rand($userIds)] : null,
                    'parent_id'   => null,
                    'name'        => $hasUser ? null : fake()->name(),
                    'email'       => $hasUser ? null : fake()->safeEmail(),
                    'body'        => fake()->paragraphs(rand(1, 3), true),
                    'is_approved' => rand(1, 10) > 4, // 60% approved
                    'created_at'  => $createdAt,
                    'updated_at'  => $createdAt,
                ];

                // Generate Replies for this Comment
                $replyCount = rand(self::REPLIES_PER_COMMENT_MIN, self::REPLIES_PER_COMMENT_MAX);
                for ($j = 0; $j < $replyCount; $j++) {
                    $replyHasUser = rand(1, 10) > 2;
                    // Replies are created after the parent comment
                    $replyCreatedAt = fake()->dateTimeBetween($createdAt)->format('Y-m-d H:i:s');

                    $commentsChunk[] = [
                        'id'          => (string) Str::orderedUuid(),
                        'post_id'     => $postId,
                        'user_id'     => $replyHasUser ? $userIds[array_rand($userIds)] : null,
                        'parent_id'   => $topLevelId,
                        'name'        => $replyHasUser ? null : fake()->name(),
                        'email'       => $replyHasUser ? null : fake()->safeEmail(),
                        'body'        => fake()->paragraphs(rand(1, 2), true),
                        'is_approved' => rand(1, 10) > 4,
                        'created_at'  => $replyCreatedAt,
                        'updated_at'  => $replyCreatedAt,
                    ];
                }

                // Insert in chunks to prevent memory overflow
                if (count($commentsChunk) >= self::CHUNK_SIZE) {
                    DB::table('engagement_comments')->insert($commentsChunk);
                    $totalInserted += count($commentsChunk);
                    $this->command?->info("Inserted {$totalInserted} comments/replies...");
                    $commentsChunk = []; // Free memory
                }
            }
        }

        // Insert any remaining records in the final chunk
        if (!empty($commentsChunk)) {
            DB::table('engagement_comments')->insert($commentsChunk);
            $totalInserted += count($commentsChunk);
        }

        $this->command?->info("Comment seeding completed. Total inserted: {$totalInserted}.");
    }
}