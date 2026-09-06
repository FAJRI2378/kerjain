<?php

namespace Tests\Feature\Hire;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VerificationTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function payload(User $hirer): array
    {
        return [
            'business_name' => 'Soto Ayam Pak Budi',
            'business_type' => 'Kuliner / F&B',
            'address' => 'Jl. Raya Cakung No. 1',
            'phone' => $hirer->phone,
            'name' => $hirer->name,
            'store_photo' => UploadedFile::fake()->image('store.jpg'),
        ];
    }

    public function test_hirer_can_submit_verification(): void
    {
        Storage::fake('public');
        $hirer = $this->hirer();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/hire/verification', $this->payload($hirer))
            ->assertOk()
            ->assertJsonPath('message', 'Data verifikasi berhasil dikirim! Verifikasi sedang diproses.');

        $this->assertDatabaseHas('verifications', [
            'user_id' => $hirer->id,
            'role' => 'hirer',
            'status' => 'pending',
        ]);

        $verification = Verification::where('user_id', $hirer->id)->first();
        $this->assertSame('Soto Ayam Pak Budi', $verification->data['business_name']);
        $this->assertNotNull($verification->data['photo_path']);
        $this->assertSame('Kuliner / F&B', $verification->data['business_type']);

        Storage::disk('public')->assertExists($verification->data['photo_path']);
    }

    public function test_resubmission_replaces_pending_verification(): void
    {
        Storage::fake('public');
        $hirer = $this->hirer();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/hire/verification', $this->payload($hirer))
            ->assertOk();

        $first = Verification::where('user_id', $hirer->id)->first();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/hire/verification', array_merge($this->payload($hirer), [
                'business_name' => 'Soto Ayam Pak Budi Cabang 2',
            ]))
            ->assertOk();

        $this->assertDatabaseCount('verifications', 1);
        $this->assertDatabaseHas('verifications', [
            'id' => $first->id,
            'status' => 'pending',
        ]);
        $updated = Verification::where('user_id', $hirer->id)->first();
        $this->assertSame('Soto Ayam Pak Budi Cabang 2', $updated->data['business_name']);
    }

    public function test_submission_requires_store_photo(): void
    {
        $hirer = $this->hirer();
        $payload = $this->payload($hirer);
        unset($payload['store_photo']);

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/hire/verification', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('store_photo');

        $this->assertDatabaseCount('verifications', 0);
    }

    public function test_freelancer_cannot_submit_hire_verification(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->postJson('/api/hire/verification', [
                'business_name' => 'Bukan UMKM',
                'business_type' => 'Lainnya',
                'address' => 'Jakarta',
                'phone' => '081234567890',
                'name' => 'Andi',
                'store_photo' => UploadedFile::fake()->image('store.jpg'),
            ])
            ->assertStatus(403);
    }
}