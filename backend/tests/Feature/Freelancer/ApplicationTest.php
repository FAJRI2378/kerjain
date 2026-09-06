<?php

namespace Tests\Feature\Freelancer;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function worker(): User
    {
        return User::factory()->freelancer()->create();
    }

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Konten & Media', 'slug' => 'konten-media']);
    }

    private function pendingApplication(Task $task, int $workerId): TaskApplication
    {
        return TaskApplication::create(['task_id' => $task->id, 'worker_id' => $workerId, 'status' => 'pending']);
    }

    public function test_freelancer_can_list_their_applications(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
            'budget' => 75000,
        ]);
        $this->pendingApplication($task, $worker->id);

        $this->actingAs($worker, 'sanctum')
            ->getJson('/api/freelancer/applications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending')
            ->assertJsonPath('data.0.job.id', $task->id)
            ->assertJsonPath('data.0.job.reward', 75000);
    }

    public function test_applications_expose_real_task_status(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->reviewing()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);
        TaskApplication::create(['task_id' => $task->id, 'worker_id' => $worker->id, 'status' => 'accepted']);

        $this->actingAs($worker, 'sanctum')
            ->getJson('/api/freelancer/applications')
            ->assertOk()
            ->assertJsonPath('data.0.status', 'accepted')
            ->assertJsonPath('data.0.job.status', 'reviewing');
    }

    public function test_freelancer_can_cancel_pending_application(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);
        $application = $this->pendingApplication($task, $worker->id);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/freelancer/applications/{$application->id}/cancel")
            ->assertOk();

        $this->assertDatabaseHas('task_applications', ['id' => $application->id, 'status' => 'cancelled']);
    }

    public function test_cannot_cancel_processed_application(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);
        $application = TaskApplication::create(['task_id' => $task->id, 'worker_id' => $worker->id, 'status' => 'accepted']);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/freelancer/applications/{$application->id}/cancel")
            ->assertStatus(409);
    }

    public function test_cannot_cancel_other_users_application(): void
    {
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);
        $application = $this->pendingApplication($task, $this->worker()->id);

        $this->actingAs($this->worker(), 'sanctum')
            ->postJson("/api/freelancer/applications/{$application->id}/cancel")
            ->assertStatus(403);
    }
}