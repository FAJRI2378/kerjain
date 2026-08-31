<?php

namespace Tests\Feature\Hire;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptApplicationTest extends TestCase
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

    public function test_hirer_can_accept_application(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = Task::factory()->approved()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);
        $application = TaskApplication::create([
            'task_id' => $task->id,
            'worker_id' => $worker->id,
            'status' => 'pending',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/applications/{$application->id}/accept")
            ->assertOk()
            ->assertJsonPath('message', 'Worker assigned successfully.');

        $this->assertDatabaseHas('task_applications', [
            'id' => $application->id,
            'status' => 'accepted',
        ]);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'worker_id' => $worker->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_accepting_application_rejects_other_pending_applicants(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = Task::factory()->approved()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $accepted = TaskApplication::create(['task_id' => $task->id, 'worker_id' => $worker->id, 'status' => 'pending']);
        $rejected = TaskApplication::create(['task_id' => $task->id, 'worker_id' => User::factory()->freelancer()->create()->id, 'status' => 'pending']);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/applications/{$accepted->id}/accept");

        $this->assertDatabaseHas('task_applications', [
            'id' => $rejected->id,
            'status' => 'rejected',
        ]);
    }

    public function test_cannot_accept_application_for_non_owned_task(): void
    {
        $otherHirer = User::factory()->hirer()->create();
        $task = Task::factory()->approved()->create([
            'owner_id' => $otherHirer->id,
            'category_id' => $this->category()->id,
        ]);
        $application = TaskApplication::create([
            'task_id' => $task->id,
            'worker_id' => $this->worker()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/applications/{$application->id}/accept")
            ->assertStatus(403);
    }

    public function test_hirer_can_reject_application(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->approved()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);
        $application = TaskApplication::create([
            'task_id' => $task->id,
            'worker_id' => $this->worker()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/applications/{$application->id}/reject")
            ->assertOk()
            ->assertJsonPath('message', 'Application rejected.');

        $this->assertDatabaseHas('task_applications', [
            'id' => $application->id,
            'status' => 'rejected',
        ]);
    }

    public function test_hirer_can_list_applicants(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->approved()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);
        TaskApplication::create(['task_id' => $task->id, 'worker_id' => $this->worker()->id, 'status' => 'pending']);

        $this->actingAs($hirer, 'sanctum')
            ->getJson("/api/tasks/{$task->id}/applicants")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }
}
