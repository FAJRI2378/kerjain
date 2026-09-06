<?php

namespace Tests\Feature\Freelancer;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_freelancer_can_submit_id_verification(): void
    {
        $freelancer = User::factory()->freelancer()->create();

        $this->actingAs($freelancer, 'sanctum')
            ->postJson('/api/freelancer/verification', [
                'phone' => '081234567890',
                'email' => 'andi@worker.id',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder_name' => 'Andi Pratama',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Data verifikasi berhasil dikirim! Verifikasi sedang diproses.');

        $this->assertDatabaseHas('verifications', [
            'user_id' => $freelancer->id,
            'role' => 'freelancer',
            'status' => 'pending',
        ]);
    }

    public function test_verification_with_missing_fields_fails(): void
    {
        $freelancer = User::factory()->freelancer()->create();

        $this->actingAs($freelancer, 'sanctum')
            ->postJson('/api/freelancer/verification', [
                'phone' => '081234567890',
            ])
            ->assertStatus(422);
    }

    public function test_hirer_cannot_submit_freelancer_verification(): void
    {
        $hirer = User::factory()->hirer()->create();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/freelancer/verification', [
                'phone' => '081234567890',
                'email' => 'budi@umkm.id',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
            ])
            ->assertStatus(403);
    }
}