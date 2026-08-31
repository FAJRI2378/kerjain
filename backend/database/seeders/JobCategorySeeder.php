<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pemasaran', 'slug' => 'pemasaran', 'icon' => null],
            ['name' => 'Fotografi', 'slug' => 'fotografi', 'icon' => null],
            ['name' => 'Live Host', 'slug' => 'live-host', 'icon' => null],
            ['name' => 'Admin', 'slug' => 'admin', 'icon' => null],
            ['name' => 'Desain', 'slug' => 'desain', 'icon' => null],
        ];

        foreach ($categories as $category) {
            JobCategory::create($category);
        }
    }
}
