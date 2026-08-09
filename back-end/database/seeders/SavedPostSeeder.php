<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class SavedPostSeeder extends Seeder
{
    private const SAVED_COUNT = 2000;
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $this->command?->info('Seeding saved posts...');

        $postIds = DB::table('content_posts')->where('is_published', true)->pluck('id')->all();
        $userIds = User::pluck('id')->all();

        if (empty($postIds) || empty($userIds)) {
            $this->command?->error('No published posts or users found.');
            return;
        }

        $savedPosts = [];
        $existingPairs = []; // Tracker for unique constraint

        while (count($savedPosts) < self::SAVED_COUNT) {
            $userId = $userIds[array_rand($userIds)];
            $postId = $postIds[array_rand($postIds)];
            $pairKey = $userId . '|' . $postId;

            if (!isset($existingPairs[$pairKey])) {
                $existingPairs[$pairKey] = true;
                $savedPosts[] = [
                    'id'       => (string) Str::orderedUuid(),
                    'user_id'  => $userId,
                    'post_id'  => $postId,
                    'saved_at' => fake()->dateTimeBetween('-3 months')->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->command?->info('Inserting saved posts in chunks...');
        foreach (array_chunk($savedPosts, self::CHUNK_SIZE) as $chunk) {
            DB::table('reader_saved_posts')->insert($chunk);
        }

        $this->command?->info('Saved posts seeding completed.');
    }
}