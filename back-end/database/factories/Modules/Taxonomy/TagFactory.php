<?php

namespace Database\Factories\Modules\Content;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\Tag;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = $this->faker->word() . '-' . Str::random(6);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}