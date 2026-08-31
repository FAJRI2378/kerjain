<?php

namespace Tests\Feature\Admin;

use App\Models\JobCategory;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_admin_can_view_settings(): void
    {
        Setting::set('platform_commission', '10');
        Setting::set('auto_approve_jobs', 'false');

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.platform_commission', 10)
            ->assertJsonPath('data.auto_approve_jobs', false);
    }

    public function test_admin_can_update_settings(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->putJson('/api/admin/settings', [
                'platform_commission' => 15,
                'auto_approve_jobs' => true,
                'maintenance_mode' => false,
                'email_notifications' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('settings', ['key' => 'platform_commission', 'value' => '15']);
        $this->assertDatabaseHas('settings', ['key' => 'auto_approve_jobs', 'value' => 'true']);
    }

    public function test_invalid_commission_fails(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->putJson('/api/admin/settings', [
                'platform_commission' => 100,
                'auto_approve_jobs' => true,
                'maintenance_mode' => false,
                'email_notifications' => true,
            ])
            ->assertStatus(422);
    }

    public function test_non_admin_cannot_update_settings(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->putJson('/api/admin/settings', [
                'platform_commission' => 10,
                'auto_approve_jobs' => false,
                'maintenance_mode' => false,
                'email_notifications' => true,
            ])
            ->assertStatus(403);
    }
}
