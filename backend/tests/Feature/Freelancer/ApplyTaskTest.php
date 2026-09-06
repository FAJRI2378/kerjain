<?php

namespace Tests\Feature\Freelancer;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplyTaskTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->verified()->create();
    }

    private function freelancer(): User
    {
        return User::factory()->freelancer()->verified()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    public function test_unverified_freelancer_cannot_apply(): void
    {
        $worker = User::factory()->freelancer()->create(['is_verified' => false]);
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(403)
            ->assertJsonValidationErrors('verification');

        $this->assertDatabaseCount('task_applications', 0);
    }

    public function test_freelancer_can_apply_to_approved_task(): void
    {
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $response = $this->actingAs($this->freelancer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply");

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Application submitted.');

        $this->assertDatabaseHas('task_applications', [
            'task_id' => $task->id,
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_application_is_prevented(): void
    {
        $worker = $this->freelancer();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        TaskApplication::create([
            'task_id' => $task->id,
            'worker_id' => $worker->id,
            'status' => 'pending',
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(409);
    }

    public function test_cannot_apply_to_own_task(): void
    {
        $worker = $this->freelancer();
        $task = Task::factory()->approved()->create([
            'owner_id' => $worker->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(403);
    }

    public function test_cannot_apply_to_pending_task(): void
    {
        $task = Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(409);
    }

    public function test_apply_requires_phone_in_profile(): void
    {
        $worker = User::factory()->freelancer()->verified()->create(['phone' => null]);
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseCount('task_applications', 0);
    }

    public function test_hirer_cannot_apply_to_task(): void
    {
        $task = Task::factory()->approved()->create([
            'owner_id' => User::factory()->hirer()->verified()->create()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/apply")
            ->assertStatus(403);
    }
}