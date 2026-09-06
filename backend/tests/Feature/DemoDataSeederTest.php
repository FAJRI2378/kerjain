<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Rating;
use App\Models\Task;
use App\Models\TaskApplication;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\JobCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_tasks_with_varied_statuses(): void
    {
        $this->seed(DatabaseSeeder::class);

        $statuses = Task::query()->distinct()->pluck('status');

        foreach ([
            Task::STATUS_APPROVED,
            Task::STATUS_PENDING,
            Task::STATUS_REJECTED,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_REVIEWING,
            Task::STATUS_COMPLETED,
            Task::STATUS_CANCELLED,
        ] as $status) {
            $this->assertTrue(
                $statuses->contains($status),
                "Seeded tasks do not include status '{$status}'."
            );
        }

        $this->assertGreaterThanOrEqual(8, Task::where('status', Task::STATUS_APPROVED)->count());
        $this->assertGreaterThan(0, Task::where('status', Task::STATUS_REJECTED)
            ->whereNotNull('rejection_reason')->count());
        $this->assertGreaterThan(0, Task::whereNotNull('worker_id')
            ->whereIn('status', [Task::STATUS_IN_PROGRESS, Task::STATUS_REVIEWING, Task::STATUS_COMPLETED])->count());
    }

    public function test_seeder_creates_applications_invoices_and_ratings(): void
    {
        $this->seed(DatabaseSeeder::class);

        $completed = Task::where('status', Task::STATUS_COMPLETED)->get();

        $this->assertGreaterThan(0, TaskApplication::count());
        $this->assertGreaterThan(0, TaskApplication::where('status', 'accepted')->count());
        $this->assertGreaterThan(0, TaskApplication::where('status', 'pending')->count());
        $this->assertSame($completed->count(), Invoice::count());
        $this->assertGreaterThan(0, Rating::count());
        $this->assertGreaterThan(0, Wallet::count());
        $this->assertGreaterThan(0, WalletTransaction::count());

        foreach ($completed as $task) {
            $this->assertNotNull($task->invoice, "Completed task '{$task->title}' has no invoice.");
            $this->assertSame($task->budget, $task->invoice->amount);
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(JobCategorySeeder::class);
        $this->seed(DemoDataSeeder::class);

        $taskCount = Task::count();
        $applicationCount = TaskApplication::count();

        $this->seed(DemoDataSeeder::class);

        $this->assertSame($taskCount, Task::count());
        $this->assertSame($applicationCount, TaskApplication::count());
    }
}