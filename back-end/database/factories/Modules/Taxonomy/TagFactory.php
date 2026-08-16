<?php

namespace Database\Factories\Modules\Taxonomy;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Taxonomy\Models\Tag;
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