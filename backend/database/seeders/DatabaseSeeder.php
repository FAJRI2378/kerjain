<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            JobCategorySeeder::class,
            SettingSeeder::class,
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin KERJAIN',
            'email' => 'admin@kerjain.id',
        ]);

        User::factory()->hirer()->verified()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@umkm.id',
        ]);

        User::factory()->freelancer()->verified()->create([
            'name' => 'Andi Pratama',
            'email' => 'andi@worker.id',
        ]);

        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
