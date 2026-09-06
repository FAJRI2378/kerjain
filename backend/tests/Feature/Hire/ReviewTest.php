<?php

namespace Tests\Feature\Hire;

use App\Models\JobCategory;
use App\Models\Rating;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
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

    private function completedTask(User $hirer, ?User $worker = null): Task
    {
        $worker = $worker ?? $this->worker();

        return Task::factory()->withWorker($worker)->completed()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'budget' => 350000,
        ]);
    }

    public function test_hirer_can_list_completed_tasks_with_review_state(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = $this->completedTask($hirer, $worker);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/tasks/completed')
            ->assertOk()
            ->assertJsonCount(1, 'data.tasks')
            ->assertJsonPath('data.tasks.0.id', $task->id)
            ->assertJsonPath('data.tasks.0.worker_name', $worker->name)
            ->assertJsonPath('data.tasks.0.worker_id', $worker->id)
            ->assertJsonPath('data.tasks.0.budget', 350000)
            ->assertJsonPath('data.tasks.0.has_reviewed', false);
    }

    public function test_hirer_can_create_review_for_completed_task(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = $this->completedTask($hirer, $worker);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/hire/tasks/{$task->id}/review", [
                'worker_id' => $worker->id,
                'rating' => 5,
                'comment' => 'Sangat profesional dan tepat waktu.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('ratings', [
            'task_id' => $task->id,
            'reviewer_id' => $hirer->id,
            'reviewee_id' => $worker->id,
            'rating' => 5,
            'comment' => 'Sangat profesional dan tepat waktu.',
        ]);
    }

    public function test_hirer_can_update_existing_review(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = $this->completedTask($hirer, $worker);
        Rating::factory()->create([
            'task_id' => $task->id,
            'reviewer_id' => $hirer->id,
            'reviewee_id' => $worker->id,
            'rating' => 4,
            'comment' => 'Cukup baik.',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/hire/tasks/{$task->id}/review", [
                'worker_id' => $worker->id,
                'rating' => 5,
                'comment' => 'Diperbaiki, sangat baik.',
            ])
            ->assertOk();

        $this->assertDatabaseCount('ratings', 1);
        $this->assertDatabaseHas('ratings', [
            'task_id' => $task->id,
            'rating' => 5,
            'comment' => 'Diperbaiki, sangat baik.',
        ]);
    }

    public function test_cannot_review_non_completed_task(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = Task::factory()->withWorker($worker)->inProgress()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/hire/tasks/{$task->id}/review", [
                'worker_id' => $worker->id,
                'rating' => 5,
                'comment' => 'Coba review dini.',
            ])
            ->assertStatus(409);
    }

    public function test_cannot_review_other_hirers_task(): void
    {
        $otherHirer = $this->hirer();
        $task = $this->completedTask($otherHirer);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/hire/tasks/{$task->id}/review", [
                'worker_id' => $task->worker_id,
                'rating' => 5,
                'comment' => 'Bukan tugas saya.',
            ])
            ->assertStatus(403);
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = $this->completedTask($hirer, $worker);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/hire/tasks/{$task->id}/review", [
                'worker_id' => $worker->id,
                'rating' => 6,
                'comment' => 'Rating diluar batas.',
            ])
            ->assertStatus(422);
    }
}