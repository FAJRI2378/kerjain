<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->freelancer()->create(['name' => 'Ahmad']);
        User::factory()->hirer()->create(['name' => 'UMKM Kopi']);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/users')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        User::factory()->freelancer()->create();
        User::factory()->hirer()->create();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/users?role=hirer')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', 'hirer');
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->freelancer()->create(['name' => 'Budi Santoso']);
        User::factory()->freelancer()->create(['name' => 'Siti Rahma']);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/users?search=Budi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budi Santoso');
    }

    public function test_admin_can_toggle_user_verification(): void
    {
        $freelancer = User::factory()->freelancer()->create(['is_verified' => false]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/users/{$freelancer->id}/verify")
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $freelancer->id, 'is_verified' => true]);
    }

    public function test_admin_can_suspend_user(): void
    {
        $freelancer = User::factory()->freelancer()->create(['is_active' => true]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/users/{$freelancer->id}/suspend")
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $freelancer->id, 'is_active' => false]);
    }

    public function test_non_admin_cannot_manage_users(): void
    {
        $freelancer = User::factory()->freelancer()->create();
        $user = User::factory()->freelancer()->create();

        $this->actingAs($freelancer, 'sanctum')
            ->postJson("/api/admin/users/{$user->id}/verify")
            ->assertStatus(403);
    }
}
