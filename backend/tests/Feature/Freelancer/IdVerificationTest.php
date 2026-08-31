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
                'nik' => '3201010101010001',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Dokumen KTP berhasil dikirim! Verifikasi sedang diproses.');
    }

    public function test_verification_with_invalid_nik_fails(): void
    {
        $freelancer = User::factory()->freelancer()->create();

        $this->actingAs($freelancer, 'sanctum')
            ->postJson('/api/freelancer/verification', [
                'nik' => '123',
            ])
            ->assertStatus(422);
    }

    public function test_hirer_cannot_submit_freelancer_verification(): void
    {
        $hirer = User::factory()->hirer()->create();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/freelancer/verification', [
                'nik' => '3201010101010001',
            ])
            ->assertStatus(403);
    }
}
