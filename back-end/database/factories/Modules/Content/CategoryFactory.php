<?php

namespace Database\Factories\Modules\Content;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Models\Category;
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