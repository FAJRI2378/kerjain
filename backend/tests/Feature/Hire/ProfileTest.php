<?php

namespace Tests\Feature\Hire;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_hirer_can_update_profile(): void
    {
        $hirer = User::factory()->hirer()->create();

        $this->actingAs($hirer, 'sanctum')
            ->putJson('/api/hire/profile', [
                'business_name' => 'UMKM Snack Sejahtera',
                'category' => 'Kuliner & Makanan',
                'phone' => '081234567890',
            ])
            ->assertOk()
            ->assertJsonPath('data.business_name', 'UMKM Snack Sejahtera')
            ->assertJsonPath('data.category', 'Kuliner & Makanan');

        $this->assertDatabaseHas('business_profiles', ['business_name' => 'UMKM Snack Sejahtera']);
    }

    public function test_hirer_can_get_profile(): void
    {
        $hirer = User::factory()->hirer()->create();
        $hirer->businessProfile()->create([
            'business_name' => 'Kopi Hits',
            'category' => 'F&B',
            'phone' => '0812',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/profile')
            ->assertOk()
            ->assertJsonPath('data.business_name', 'Kopi Hits');
    }

    public function test_freelancer_cannot_access_hirer_profile(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->putJson('/api/hire/profile', [
                'business_name' => 'X',
                'category' => 'Y',
                'phone' => 'Z',
            ])
            ->assertStatus(403);
    }
}
