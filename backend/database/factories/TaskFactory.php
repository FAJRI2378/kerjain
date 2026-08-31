<?php

namespace Database\Factories;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->hirer(),
            'category_id' => JobCategory::factory(),
            'worker_id' => null,
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'budget' => fake()->numberBetween(50000, 5000000),
            'location' => fake()->city(),
            'status' => 'pending',
            'proof_url' => null,
            'deadline' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function reviewing(): static
    {
        return $this->state(fn () => ['status' => 'reviewing']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }

    public function withWorker(?User $worker = null): static
    {
        $worker = $worker ?? User::factory()->freelancer()->create();

        return $this->state(fn () => ['worker_id' => $worker->id]);
    }
}
