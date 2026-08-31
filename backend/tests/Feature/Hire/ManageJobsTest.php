<?php

namespace Tests\Feature\Hire;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageJobsTest extends TestCase
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

    public function test_hirer_sees_only_own_jobs(): void
    {
        $hirer = $this->hirer();
        $category = $this->category();

        Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
        ]);
        Task::factory()->approved()->create([
            'owner_id' => User::factory()->hirer()->create()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/jobs')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_hirer_can_complete_reviewing_task(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
    }

    public function test_hirer_cannot_complete_non_reviewing_task(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete")
            ->assertStatus(409);
    }

    public function test_hirer_cannot_complete_another_hirers_task(): void
    {
        $otherHirer = User::factory()->hirer()->create();
        $task = Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $otherHirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete")
            ->assertStatus(403);
    }
}
