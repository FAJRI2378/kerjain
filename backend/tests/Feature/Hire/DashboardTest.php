<?php

namespace Tests\Feature\Hire;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\TaskApplication;
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

    private function worker(): User
    {
        return User::factory()->freelancer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    public function test_hirer_dashboard_returns_stats(): void
    {
        $hirer = $this->hirer();
        $category = $this->category();

        Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 150000,
        ]);
        Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 100000,
        ]);
        Task::factory()->completed()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 200000,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/dashboard')
            ->assertOk()
            ->assertJsonPath('data.escrow_balance', 250000)
            ->assertJsonPath('data.total_spent', 200000)
            ->assertJsonPath('data.active_jobs_count', 2);
    }

    public function test_hirer_dashboard_counts_pending_applicants(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->approved()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        TaskApplication::create(['task_id' => $task->id, 'worker_id' => $this->worker()->id, 'status' => 'pending']);
        TaskApplication::create(['task_id' => $task->id, 'worker_id' => User::factory()->freelancer()->create()->id, 'status' => 'pending']);
        TaskApplication::create(['task_id' => $task->id, 'worker_id' => User::factory()->freelancer()->create()->id, 'status' => 'rejected']);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/dashboard')
            ->assertOk()
            ->assertJsonPath('data.applicants_count', 2);
    }

    public function test_freelancer_cannot_access_hirer_dashboard(): void
    {
        $this->actingAs($this->worker(), 'sanctum')
            ->getJson('/api/hire/dashboard')
            ->assertStatus(403);
    }
}
