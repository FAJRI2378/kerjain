<?php

namespace Database\Factories;

use App\Models\BusinessProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessProfile>
 */
class BusinessProfileFactory extends Factory
{
    protected $model = BusinessProfile::class;

    public function definition(): array
    {
        return [
            'business_name' => fake()->company(),
            'category' => fake()->randomElement(['Kuliner & Makanan', 'Fashion', 'Elektronik', 'Jasa']),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
