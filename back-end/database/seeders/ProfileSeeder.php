<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Identity\Models\User;
use Modules\Profile\Models\Profile;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('Seeding professional profiles for staff members...');
        
        // Only Admins, Editors, and Authors need professional profiles
        $staffUsers = User::role(['admin', 'editor', 'author'])->get();

        foreach ($staffUsers as $user) {
            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'avatar'              => fake()->imageUrl(400, 400, 'people', true, $user->username),
                    'bio'                 => fake()->realTextBetween(150, 300),
                    'expertise'           => fake()->jobTitle(),
                    'years_of_experience' => fake()->numberBetween(1, 15),
                    'social_links'        => array_filter([
                        'twitter'   => fake()->optional(0.7)->url(),
                        'github'    => fake()->optional(0.8)->url(),
                        'linkedin'  => fake()->optional(0.6)->url(),
                        'instagram' => fake()->optional(0.4)->url(),
                        'dribbble'  => fake()->optional(0.3)->url(),
                    ]),
                ]
            );
        }
        
        $this->command?->info('Profile seeding completed successfully.');
    }
}