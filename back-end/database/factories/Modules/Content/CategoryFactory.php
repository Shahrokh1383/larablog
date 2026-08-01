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
        $name = $this->faker->unique()->word();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}