<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Identity\Models\User;

class SubscriberSeeder extends Seeder
{
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $this->command?->info('Seeding subscribers...');
        
        $userCount = User::count();
        $subscribers = [];
        $now = now()->toDateTimeString();
        
        for ($i = 1; $i <= $userCount; $i++) {
            $subscribers[] = [
                'id'         => (string) Str::orderedUuid(),
                // Combines index with Faker to guarantee 100% uniqueness without OverflowException
                'email'      => 'sub' . $i . '_' . fake()->safeEmail(),
                'is_active'  => rand(1, 10) > 2, // 80% active, 20% inactive
                'created_at' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => $now,
            ];
            
            // Insert in chunks to maintain low memory footprint
            if (count($subscribers) >= self::CHUNK_SIZE) {
                DB::table('marketing_subscribers')->insert($subscribers);
                $subscribers = [];
            }
        }
        
        // Insert remaining records
        if (!empty($subscribers)) {
            DB::table('marketing_subscribers')->insert($subscribers);
        }

        $this->command?->info('Subscriber seeding completed.');
    }
}