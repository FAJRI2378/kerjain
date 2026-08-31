<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'freelancer',
            'phone' => fake()->phoneNumber(),
            'avatar' => null,
            'is_verified' => false,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function freelancer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'freelancer']);
    }

    public function hirer(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'hirer']);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => 'admin']);
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => ['is_verified' => true]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
