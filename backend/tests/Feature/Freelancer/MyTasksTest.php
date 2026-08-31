<?php

namespace Tests\Feature\Freelancer;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyTasksTest extends TestCase
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

    public function test_freelancer_sees_assigned_tasks(): void
    {
        $worker = $this->freelancer();
        $category = $this->category();

        Task::factory()->inProgress()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);
        Task::factory()->reviewing()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->getJson('/api/tasks/mine')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_freelancer_can_submit_proof(): void
    {
        $worker = $this->freelancer();
        $task = Task::factory()->inProgress()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/submit", [
                'proof_url' => 'https://drive.google.com/bukti-karya',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'reviewing')
            ->assertJsonPath('data.proof_url', 'https://drive.google.com/bukti-karya');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'reviewing',
            'proof_url' => 'https://drive.google.com/bukti-karya',
        ]);
    }

    public function test_worker_cannot_edit_completed_task(): void
    {
        $worker = $this->freelancer();
        $task = Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/submit", [
                'proof_url' => 'https://example.com/proof',
            ])
            ->assertStatus(409);
    }

    public function test_worker_cannot_submit_for_non_assigned_task(): void
    {
        $otherWorker = User::factory()->freelancer()->create();
        $task = Task::factory()->inProgress()->withWorker($otherWorker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/submit", [
                'proof_url' => 'https://example.com/proof',
            ])
            ->assertStatus(403);
    }

    public function test_submit_with_invalid_url_fails(): void
    {
        $worker = $this->freelancer();
        $task = Task::factory()->inProgress()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/submit", [
                'proof_url' => 'not-a-valid-url',
            ])
            ->assertStatus(422);
    }
}
