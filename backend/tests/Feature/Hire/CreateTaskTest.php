<?php

namespace Tests\Feature\Hire;

use App\Models\JobCategory;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    public function test_hirer_can_create_task(): void
    {
        $category = $this->category();

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Jasa Sebar Brosur 500 Lembar',
                'category' => $category->slug,
                'location' => 'Jakarta Selatan',
                'budget' => 150000,
                'description' => 'Sebar brosur promo toko di area pasar.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Jasa Sebar Brosur 500 Lembar')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('tasks', ['title' => 'Jasa Sebar Brosur 500 Lembar']);
    }

    public function test_create_task_requires_budget_minimum(): void
    {
        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Task',
                'category' => $this->category()->slug,
                'location' => 'Jakarta',
                'budget' => 1000,
                'description' => 'Description',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('budget');
    }

    public function test_create_task_with_missing_fields_fails(): void
    {
        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson('/api/tasks', [])
            ->assertStatus(422);
    }

    public function test_freelancer_cannot_create_task(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Task',
                'category' => $this->category()->slug,
                'location' => 'Jakarta',
                'budget' => 100000,
                'description' => 'Description',
            ])
            ->assertStatus(403);
    }

    public function test_auto_approve_setting_makes_task_approved(): void
    {
        Setting::set('auto_approve_jobs', 'true');
        $category = $this->category();

        $this->actingAs($this->hirer(), 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'Auto Approved Task',
                'category' => $category->slug,
                'location' => 'Bandung',
                'budget' => 100000,
                'description' => 'Description',
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'approved');
    }
}
