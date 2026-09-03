<?php

namespace Database\Factories\Modules\ReaderExperience;

use Modules\ReaderExperience\Models\SavedPost;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SavedPostFactory extends Factory
{
    protected $model = SavedPost::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'saved_at' => Carbon::now(),
        ];
    }
}