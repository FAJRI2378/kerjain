<?php

namespace Tests\Feature\Hire;

use App\Models\Invoice;
use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use App\Models\Wallet;
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

    public function test_hirer_can_complete_reviewing_task_and_release_escrow(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = Task::factory()->reviewing()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'budget' => 100000,
        ]);
        Wallet::create(['user_id' => $hirer->id, 'balance' => 500000]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
        $this->assertDatabaseHas('wallets', ['user_id' => $hirer->id, 'balance' => 400000]);
        $this->assertDatabaseHas('wallets', ['user_id' => $worker->id, 'balance' => 100000]);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'out',
            'title' => "Bayar Kontrak: {$task->title}",
            'amount' => 100000,
        ]);
        $this->assertDatabaseHas('invoices', ['user_id' => $hirer->id, 'task_id' => $task->id, 'amount' => 100000]);
    }

    public function test_hirer_cannot_complete_reviewing_task_without_escrow_funds(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'budget' => 100000,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/complete")
            ->assertStatus(409);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'reviewing']);
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

    public function test_hirer_can_send_reviewing_task_back_to_revision(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'proof_url' => 'https://drive.google.com/bukti',
            'proof_image' => 'proofs/1/bukti.jpg',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/revision", [
                'note' => 'Gambar bukti kurang jelas, mohon perbaiki.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.revision_note', 'Gambar bukti kurang jelas, mohon perbaiki.')
            ->assertJsonPath('data.proof_url', null);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
            'proof_url' => null,
            'proof_image' => null,
            'revision_note' => 'Gambar bukti kurang jelas, mohon perbaiki.',
        ]);
    }

    public function test_hirer_cannot_revise_non_reviewing_task(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/revision", ['note' => 'Revisi.'])
            ->assertStatus(409);
    }

    public function test_hirer_cannot_revise_another_hirers_task(): void
    {
        $otherHirer = User::factory()->hirer()->create();
        $task = Task::factory()->reviewing()->withWorker($this->worker())->create([
            'owner_id' => $otherHirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson("/api/tasks/{$task->id}/revision", ['note' => 'Revisi.'])
            ->assertStatus(403);
    }

    public function test_worker_cannot_request_revision(): void
    {
        $worker = $this->worker();
        $task = Task::factory()->reviewing()->withWorker($worker)->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/revision", ['note' => 'Revisi.'])
            ->assertStatus(403);
    }

    private function updatePayload(): array
    {
        return [
            'title' => 'Judul Tugas Baru',
            'category' => 'pemasaran',
            'location' => 'Depok',
            'budget' => 75000,
            'description' => 'Deskripsi tugas yang diperbarui.',
            'deadline' => '30 Sep 2026',
        ];
    }

    public function test_hirer_can_update_own_rejected_job_and_resubmit(): void
    {
        $hirer = $this->hirer();
        $category = $this->category();
        $task = Task::factory()->rejected()->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'rejection_reason' => 'Deskripsi kurang lengkap.',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->putJson("/api/hire/jobs/{$task->id}", $this->updatePayload())
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.title', 'Judul Tugas Baru');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'pending',
            'rejection_reason' => null,
            'budget' => 75000,
        ]);
    }

    public function test_hirer_cannot_update_running_task(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->putJson("/api/hire/jobs/{$task->id}", $this->updatePayload())
            ->assertStatus(409);
    }

    public function test_hirer_cannot_update_another_hirers_job(): void
    {
        $otherHirer = User::factory()->hirer()->create();
        $task = Task::factory()->rejected()->create([
            'owner_id' => $otherHirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->putJson("/api/hire/jobs/{$task->id}", $this->updatePayload())
            ->assertStatus(403);
    }

    public function test_hirer_can_delete_own_pending_job(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->deleteJson("/api/hire/jobs/{$task->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_hirer_cannot_delete_running_task(): void
    {
        $hirer = $this->hirer();
        $task = Task::factory()->inProgress()->withWorker($this->worker())->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->deleteJson("/api/hire/jobs/{$task->id}")
            ->assertStatus(409);
    }

    public function test_hirer_cannot_delete_another_hirers_job(): void
    {
        $otherHirer = User::factory()->hirer()->create();
        $task = Task::factory()->create([
            'owner_id' => $otherHirer->id,
            'category_id' => $this->category()->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->hirer(), 'sanctum')
            ->deleteJson("/api/hire/jobs/{$task->id}")
            ->assertStatus(403);
    }
}
