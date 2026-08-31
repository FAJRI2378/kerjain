<?php

namespace Database\Factories;

use App\Models\JobCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobCategory>
 */
class JobCategoryFactory extends Factory
{
    protected $model = JobCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'slug' => fake()->unique()->slug(),
            'icon' => null,
        ];
    }
}
