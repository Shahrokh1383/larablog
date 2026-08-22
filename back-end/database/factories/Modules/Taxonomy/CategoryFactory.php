<?php

namespace Database\Factories\Modules\Taxonomy;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Taxonomy\Models\Category;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->word() . '-' . Str::random(6);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}