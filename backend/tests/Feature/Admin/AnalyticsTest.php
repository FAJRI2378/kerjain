<?php

namespace Tests\Feature\Admin;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
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

    public function test_admin_can_view_analytics(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 150000,
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 100000,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/analytics')
            ->assertOk()
            ->assertJsonPath('data.completed_jobs', 2)
            ->assertJsonPath('data.total_transactions', 250000);
    }

    public function test_non_admin_cannot_view_analytics(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->getJson('/api/admin/analytics')
            ->assertStatus(403);
    }
}
