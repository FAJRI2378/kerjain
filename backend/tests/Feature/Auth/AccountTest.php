<?php

namespace Tests\Feature\Auth;

use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->freelancer()->create();
        Verification::create([
            'user_id' => $user->id,
            'role' => 'freelancer',
            'status' => 'pending',
            'data' => ['bank_name' => 'BCA', 'account_number' => '1234567890', 'account_holder_name' => 'Andi'],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('user.verification.status', 'pending')
            ->assertJsonPath('user.verification.account_holder_name', 'Andi');
    }

    public function test_verification_status_returns_latest_submission(): void
    {
        $user = User::factory()->freelancer()->create();
        $old = Verification::create([
            'user_id' => $user->id,
            'role' => 'freelancer',
            'status' => 'rejected',
            'data' => [],
            'admin_note' => 'Foto kurang jelas.',
        ]);
        $latest = Verification::create([
            'user_id' => $user->id,
            'role' => 'freelancer',
            'status' => 'pending',
            'data' => ['bank_name' => 'BNI'],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/verification/status')
            ->assertOk()
            ->assertJsonPath('data.id', $latest->id)
            ->assertJsonPath('data.status', 'pending');

        $this->assertNotSame($old->id, $latest->id);
    }

    public function test_verification_status_returns_null_when_none(): void
    {
        $user = User::factory()->freelancer()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/verification/status')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->freelancer()->create(['password' => 'old-password-123']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/password', [
                'current_password' => 'old-password-123',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Kata sandi berhasil diubah.');

        $this->assertNotSame('old-password-123', $user->fresh()->password);
        $this->assertTrue(password_verify('new-password-456', $user->fresh()->password));
    }

    public function test_change_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->freelancer()->create(['password' => 'old-password-123']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');
    }

    public function test_change_password_requires_confirmation(): void
    {
        $user = User::factory()->freelancer()->create(['password' => 'old-password-123']);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/account/password', [
                'current_password' => 'old-password-123',
                'password' => 'abc',
                'password_confirmation' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('password');
    }
}