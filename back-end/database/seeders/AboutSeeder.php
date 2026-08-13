<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\About\Models\TeamMember;
use Modules\Identity\Models\User;

class AboutSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Update the Site Settings singleton with requested data
        DB::table('about_site_settings')->where('id', 1)->update([
            'call_us_phone' => '+1 (234) 567-890',
            'call_us_emails' => json_encode(['LaraBlog@gmail.com']),
            'visit_address' => '123 Innovation Drive, Tech City, CA 94043',
            'social_links'  => json_encode([
                'linkedin'   => 'https://linkedin.com/company/larablog',
                'github'     => 'https://github.com/larablog',
                'twitter'    => 'https://twitter.com/larablog',
                'instagram'  => 'https://instagram.com/larablog',
                'dribbble'   => 'https://dribbble.com/larablog',
                'youtube'    => 'https://youtube.com/@larablog',
                'discord'    => 'https://discord.gg/larablog',
            ]),
            'updated_at' => now(),
        ]);

        // 2. Seed Team Members
        // Fetch 10 random users who have the admin, editor, or author role
        $roles = ['admin', 'editor', 'author'];
        
        $users = User::whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        })->inRandomOrder()->take(10)->get();

        // Clear existing team members to avoid duplicates on re-seed
        TeamMember::query()->delete();

        $sortOrder = 1;
        foreach ($users as $user) {
            TeamMember::create([
                'user_id'    => $user->id,
                'sort_order' => $sortOrder++,
                'is_active'  => true,
            ]);
        }
    }
}