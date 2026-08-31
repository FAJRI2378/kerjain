<?php

namespace Tests\Feature;

use App\Models\JobCategory;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_mode_blocks_regular_users(): void
    {
        Setting::set('maintenance_mode', 'true');
        $user = User::factory()->freelancer()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks')
            ->assertStatus(503);
    }

    public function test_maintenance_mode_allows_admin(): void
    {
        Setting::set('maintenance_mode', 'true');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/dashboard')
            ->assertOk();
    }

    public function test_maintenance_off_allows_all_users(): void
    {
        Setting::set('maintenance_mode', 'false');
        $category = JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
        Task::factory()->approved()->create([
            'owner_id' => User::factory()->hirer()->create()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk();
    }
}
