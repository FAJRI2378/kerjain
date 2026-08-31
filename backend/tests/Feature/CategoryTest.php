<?php

namespace Tests\Feature;

use App\Models\JobCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_listed_publicly(): void
    {
        JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
        JobCategory::create(['name' => 'Desain', 'slug' => 'desain']);

        $this->getJson('/api/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'icon']]]);
    }
}
