<?php

namespace Database\Factories\Modules\ReaderExperience;

use Modules\ReaderExperience\Models\PostRead;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PostReadFactory extends Factory
{
    protected $model = PostRead::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
            'read_at' => Carbon::now(),
        ];
    }
}