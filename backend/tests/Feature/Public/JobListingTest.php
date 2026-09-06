<?php

namespace Tests\Feature\Public;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobListingTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $name = 'Soto Ayam Pak Budi'): User
    {
        return User::factory()->hirer()->create(['name' => $name]);
    }

    private function category(string $name, string $slug): JobCategory
    {
        return JobCategory::create(['name' => $name, 'slug' => $slug]);
    }

    private function approvedTask(array $overrides = []): Task
    {
        return Task::factory()->approved()->withWorker()->create($overrides);
    }

    public function test_guest_can_list_approved_jobs_without_token(): void
    {
        $this->approvedTask([
            'title' => 'Foto Produk Menu',
            'owner_id' => $this->owner()->id,
            'category_id' => $this->category('Konten & Media', 'konten')->id,
        ]);

        $this->getJson('/api/jobs')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_non_approved_jobs_are_hidden(): void
    {
        $category = $this->category('Operasional', 'operasional');
        $owner = $this->owner();

        Task::factory()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);
        Task::factory()->inProgress()->withWorker()->create(['owner_id' => $owner->id, 'category_id' => $category->id]);

        $this->getJson('/api/jobs')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_search_filters_by_title_and_owner(): void
    {
        $category = $this->category('Desain', 'desain');

        $this->approvedTask([
            'title' => 'Desain Poster Grand Opening',
            'owner_id' => $this->owner('Kopi Senja')->id,
            'category_id' => $category->id,
        ]);
        $this->approvedTask([
            'title' => 'Packing Snack Box',
            'owner_id' => $this->owner('Dapur Mama')->id,
            'category_id' => $category->id,
        ]);

        $this->getJson('/api/jobs?search=desain')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Desain Poster Grand Opening');

        $this->getJson('/api/jobs?search=Dapur Mama')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Packing Snack Box');
    }

    public function test_category_slug_filters_jobs(): void
    {
        $category = $this->category('Administrasi', 'administrasi');
        $other = $this->category('Operasional', 'operasional');
        $owner = $this->owner();

        $this->approvedTask(['title' => 'Input Stok', 'owner_id' => $owner->id, 'category_id' => $category->id]);
        $this->approvedTask(['title' => 'Packing Acara', 'owner_id' => $owner->id, 'category_id' => $other->id]);

        $this->getJson('/api/jobs?category=administrasi')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category.slug', 'administrasi');
    }

    public function test_results_are_paginated_six_per_page(): void
    {
        $owner = $this->owner();
        $category = $this->category('Desain', 'desain');

        Task::factory()->count(7)->approved()->withWorker()->create([
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        $this->getJson('/api/jobs?per_page=6')
            ->assertOk()
            ->assertJsonCount(6, 'data')
            ->assertJsonPath('meta.per_page', 6)
            ->assertJsonPath('meta.total', 7)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.current_page', 1);

        $this->getJson('/api/jobs?per_page=6&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_card_fields_are_available_to_frontend(): void
    {
        $category = $this->category('Design', 'design');
        $owner = $this->owner('Budi Coffee');

        $this->approvedTask([
            'title' => 'Buat Logo Kopi',
            'description' => 'Desain logo minimalis.',
            'budget' => 75000,
            'location' => 'Depok',
            'owner_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        $this->getJson('/api/jobs')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'Buat Logo Kopi')
            ->assertJsonPath('data.0.description', 'Desain logo minimalis.')
            ->assertJsonPath('data.0.budget', 75000)
            ->assertJsonPath('data.0.location', 'Depok')
            ->assertJsonPath('data.0.category.name', 'Design')
            ->assertJsonPath('data.0.owner.name', 'Budi Coffee');
    }
}