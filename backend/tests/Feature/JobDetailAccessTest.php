<?php

namespace Tests\Feature;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDetailAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function hire(): User
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

    public function test_admin_can_view_pending_job_detail(): void
    {
        $task = Task::factory()->create([
            'owner_id' => $this->hire()->id,
            'category_id' => $this->category()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_owner_can_view_own_rejected_job_detail_with_reason(): void
    {
        $owner = $this->hire();
        $task = Task::factory()->rejected()->create([
            'owner_id' => $owner->id,
            'category_id' => $this->category()->id,
            'rejection_reason' => 'Deskripsi kurang lengkap.',
        ]);

        $this->actingAs($owner, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.rejection_reason', 'Deskripsi kurang lengkap.');
    }

    public function test_assigned_worker_can_view_in_progress_job_detail(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->inProgress()->withWorker($worker)->create([
            'owner_id' => $this->hire()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');
    }

    public function test_applicant_can_view_job_detail_and_applicants_count(): void
    {
        $worker = $this->worker();
        $owner = $this->hire();
        $task = Task::factory()->approved()->create([
            'owner_id' => $owner->id,
            'category_id' => $this->category()->id,
        ]);
        TaskApplication::create([
            'task_id' => $task->id,
            'worker_id' => $worker->id,
            'status' => 'pending',
        ]);

        $this->actingAs($worker, 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.applicants_count', 1);
    }

    public function test_random_hirer_cannot_view_another_hirers_pending_job(): void
    {
        $task = Task::factory()->create([
            'owner_id' => $this->hire()->id,
            'category_id' => $this->category()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->hire(), 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertStatus(404);
    }
}