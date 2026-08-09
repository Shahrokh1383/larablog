<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class ProfileSeeder extends Seeder
{
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $this->command?->info('Seeding profiles for all users...');

        $userIds = User::pluck('id')->all();
        $now = now()->toDateTimeString();
        $profiles = [];

        foreach ($userIds as $userId) {
            $profiles[] = [
                'id'                  => (string) Str::orderedUuid(),
                'user_id'             => $userId,
                'avatar'              => fake()->imageUrl(400, 400, 'people', true),
                'bio'                 => fake()->realTextBetween(150, 300),
                'expertise'           => fake()->jobTitle(),
                'years_of_experience' => fake()->numberBetween(1, 15),
                'social_links'        => json_encode(array_filter([
                    'twitter'   => fake()->optional(0.7)->url(),
                    'github'    => fake()->optional(0.8)->url(),
                    'linkedin'  => fake()->optional(0.6)->url(),
                    'instagram' => fake()->optional(0.4)->url(),
                    'dribbble'  => fake()->optional(0.3)->url(),
                ])),
                'created_at'          => $now,
                'updated_at'          => $now,
            ];
        }

        $this->command?->info('Inserting profiles in chunks...');
        foreach (array_chunk($profiles, self::CHUNK_SIZE) as $chunk) {
            DB::table('profiles')->insert($chunk);
        }

        $this->command?->info('Profile seeding completed successfully.');
    }
}