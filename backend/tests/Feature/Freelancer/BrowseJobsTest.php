<?php

namespace Tests\Feature\Freelancer;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrowseJobsTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function freelancer(): User
    {
        return User::factory()->freelancer()->create();
    }

    private function category(string $name = 'Pemasaran', string $slug = 'pemasaran'): JobCategory
    {
        return JobCategory::create(['name' => $name, 'slug' => $slug]);
    }

    public function test_freelancer_can_browse_approved_tasks(): void
    {
        $category = $this->category();
        Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'title' => 'Sebar Brosur',
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Sebar Brosur');
    }

    public function test_pending_tasks_are_not_visible_to_freelancers(): void
    {
        $category = $this->category();
        Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_own_tasks_are_excluded_from_listing(): void
    {
        $worker = $this->freelancer();
        $category = $this->category();

        Task::factory()->approved()->create([
            'owner_id' => $worker->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($worker, 'sanctum')
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_can_search_tasks_by_title(): void
    {
        $category = $this->category();
        $owner = $this->hirer();

        Task::factory()->approved()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Foto Produk Kuliner',
        ]);
        Task::factory()->approved()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Input Data Nota',
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson('/api/tasks?search=Foto')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Foto Produk Kuliner');
    }

    public function test_can_filter_by_category_slug(): void
    {
        $pemasaran = $this->category('Pemasaran', 'pemasaran');
        $desain = $this->category('Desain', 'desain');
        $owner = $this->hirer();

        Task::factory()->approved()->create([
            'owner_id' => $owner->id,
            'category_id' => $pemasaran->id,
        ]);
        Task::factory()->approved()->create([
            'owner_id' => $owner->id,
            'category_id' => $desain->id,
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson('/api/tasks?category=pemasaran')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_view_single_approved_task(): void
    {
        $category = $this->category();
        $task = Task::factory()->approved()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_pending_task_detail_is_not_visible(): void
    {
        $category = $this->category();
        $task = Task::factory()->create([
            'owner_id' => $this->hirer()->id,
            'category_id' => $category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->freelancer(), 'sanctum')
            ->getJson("/api/tasks/{$task->id}")
            ->assertStatus(404);
    }
}
