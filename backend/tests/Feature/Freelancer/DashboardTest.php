<?php

namespace Tests\Feature\Freelancer;

use App\Models\JobCategory;
use App\Models\Rating;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function freelancer(): User
    {
        return User::factory()->freelancer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    public function test_freelancer_dashboard_returns_stats(): void
    {
        $worker = $this->freelancer();
        $category = $this->category();

        Task::factory()->completed()->withWorker($worker)->create([
            'category_id' => $category->id,
            'budget' => 150000,
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'category_id' => $category->id,
            'budget' => 100000,
        ]);
        Task::factory()->inProgress()->withWorker($worker)->create([
            'category_id' => $category->id,
            'budget' => 200000,
        ]);

        Rating::create([
            'task_id' => Task::factory()->completed()->create(['category_id' => $category->id])->id,
            'reviewer_id' => $this->hirer()->id,
            'reviewee_id' => $worker->id,
            'rating' => 5,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->getJson('/api/freelancer/dashboard')
            ->assertOk()
            ->assertJsonPath('data.balance', 250000)
            ->assertJsonPath('data.completed_tasks', 2)
            ->assertJsonPath('data.active_tasks', 1)
            ->assertJsonPath('data.average_rating', 5);
    }

    public function test_non_freelancer_cannot_access_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/freelancer/dashboard')
            ->assertStatus(403);
    }
}
