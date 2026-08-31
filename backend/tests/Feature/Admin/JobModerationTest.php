<?php

namespace Tests\Feature\Admin;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobModerationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    public function test_admin_can_list_all_tasks(): void
    {
        $category = $this->category();
        Task::factory()->create(['owner_id' => $this->hirer()->id, 'category_id' => $category->id, 'status' => 'pending']);
        Task::factory()->approved()->create(['owner_id' => $this->hirer()->id, 'category_id' => $category->id]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_tasks_by_status(): void
    {
        $category = $this->category();
        Task::factory()->create(['owner_id' => $this->hirer()->id, 'category_id' => $category->id, 'status' => 'pending']);
        Task::factory()->approved()->create(['owner_id' => $this->hirer()->id, 'category_id' => $category->id]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/tasks?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_admin_can_approve_pending_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/tasks/{$task->id}/approve")
            ->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'approved']);
    }

    public function test_admin_can_reject_pending_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/tasks/{$task->id}/reject")
            ->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'rejected']);
    }

    public function test_cannot_approve_already_approved_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/tasks/{$task->id}/approve")
            ->assertStatus(409);
    }

    public function test_admin_can_delete_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->rejected()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->deleteJson("/api/admin/tasks/{$task->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cannot_delete_in_progress_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->inProgress()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->deleteJson("/api/admin/tasks/{$task->id}")
            ->assertStatus(409);
    }

    public function test_non_admin_cannot_moderate_tasks(): void
    {
        $category = $this->category();
        $task = Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/admin/tasks/{$task->id}/approve")
            ->assertStatus(403);
    }
}
