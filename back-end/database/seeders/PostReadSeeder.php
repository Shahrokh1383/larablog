<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class PostReadSeeder extends Seeder
{
    private const READS_COUNT = 2000;
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $this->command?->info('Seeding post reads...');

        $postIds = DB::table('content_posts')->where('is_published', true)->pluck('id')->all();
        $userIds = User::pluck('id')->all();

        if (empty($postIds) || empty($userIds)) {
            $this->command?->error('No published posts or users found.');
            return;
        }

        $postReads = [];

        // NEW: Guarantee every published post has at least 1 read
        foreach ($postIds as $postId) {
            $postReads[] = [
                'id'       => (string) Str::orderedUuid(),
                'user_id'  => $userIds[array_rand($userIds)],
                'post_id'  => $postId,
                'read_at'  => fake()->dateTimeBetween('-1 month')->format('Y-m-d H:i:s'),
            ];
        }

        // Generate the remaining random reads
        for ($i = 0; $i < self::READS_COUNT; $i++) {
            $postReads[] = [
                'id'       => (string) Str::orderedUuid(),
                'user_id'  => $userIds[array_rand($userIds)],
                'post_id'  => $postIds[array_rand($postIds)],
                'read_at'  => fake()->dateTimeBetween('-1 month')->format('Y-m-d H:i:s'),
            ];
        }

        $this->command?->info('Inserting post reads in chunks...');
        foreach (array_chunk($postReads, self::CHUNK_SIZE) as $chunk) {
            DB::table('reader_post_reads')->insert($chunk);
        }

        $this->command?->info('Post reads seeding completed.');
    }
}