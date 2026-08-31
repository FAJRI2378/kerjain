<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskApplication>
 */
class TaskApplicationFactory extends Factory
{
    protected $model = TaskApplication::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory()->approved(),
            'worker_id' => User::factory()->freelancer(),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'accepted']);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => 'rejected']);
    }
}
