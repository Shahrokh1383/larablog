<?php

namespace Database\Factories\Modules\Articles;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Articles\Models\Post;
use Shared\Models\User;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'title'          => $title,
            'slug'           => Str::slug($title) . '-' . Str::random(5),
            'body'           => $this->faker->paragraphs(3, true),
            'is_published'   => false,
            'reading_time'   => 1,
            'user_id'        => User::factory(),
        ];
    }
}