<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationQueueTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function pendingHirerSubmission(): Verification
    {
        $hirer = User::factory()->hirer()->create(['is_verified' => false]);

        return Verification::create([
            'user_id' => $hirer->id,
            'role' => 'hirer',
            'status' => 'pending',
            'data' => [
                'business_name' => 'Kopi Senja Nusantara',
                'category' => 'Kuliner / F&B',
                'address' => 'Jl. Ahmad Yani No. 45, Bekasi',
                'photo_path' => 'business-profile/store.jpg',
                'note' => 'Foto tampak depan kedai.',
            ],
        ]);
    }

    public function test_admin_can_list_pending_verifications(): void
    {
        $submission = $this->pendingHirerSubmission();
        $this->pendingHirerSubmission();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/verifications/pending')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $submission->id)
            ->assertJsonPath('data.0.role', 'hirer')
            ->assertJsonPath('data.0.business_name', 'Kopi Senja Nusantara');
    }

    public function test_admin_can_filter_verification_by_status(): void
    {
        $this->pendingHirerSubmission();
        $approved = $this->pendingHirerSubmission();
        $approved->update(['status' => 'approved', 'reviewed_by' => $this->admin()->id, 'reviewed_at' => now()]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/verifications?status=approved')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approved->id);
    }

    public function test_admin_can_filter_verification_by_role(): void
    {
        $this->pendingHirerSubmission();
        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/verifications?status=pending&role=freelancer')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_search_verification_by_user(): void
    {
        $submission = $this->pendingHirerSubmission();
        $user = $submission->user;

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/verifications?search=' . urlencode($user->name))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $submission->id);
    }

    public function test_admin_can_approve_pending_verification(): void
    {
        $admin = $this->admin();
        $submission = $this->pendingHirerSubmission();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/verifications/{$submission->id}/approve")
            ->assertOk();

        $this->assertDatabaseHas('verifications', [
            'id' => $submission->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $submission->user_id,
            'is_verified' => true,
        ]);
    }

    public function test_admin_can_reject_pending_verification_with_reason(): void
    {
        $admin = $this->admin();
        $submission = $this->pendingHirerSubmission();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/admin/verifications/{$submission->id}/reject", [
                'reason' => 'Foto toko terlalu gelap.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('verifications', [
            'id' => $submission->id,
            'status' => 'rejected',
            'admin_note' => 'Foto toko terlalu gelap.',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $submission->user_id,
            'is_verified' => false,
        ]);
    }

    public function test_cannot_process_already_processed_verification(): void
    {
        $submission = $this->pendingHirerSubmission();
        $submission->update(['status' => 'approved', 'reviewed_by' => $this->admin()->id, 'reviewed_at' => now()]);

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/verifications/{$submission->id}/approve")
            ->assertStatus(409);
    }

    public function test_reject_requires_reason(): void
    {
        $submission = $this->pendingHirerSubmission();

        $this->actingAs($this->admin(), 'sanctum')
            ->postJson("/api/admin/verifications/{$submission->id}/reject", [])
            ->assertStatus(422);
    }

    public function test_non_admin_cannot_access_verification_queue(): void
    {
        $hirer = User::factory()->hirer()->create();

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/admin/verifications/pending')
            ->assertStatus(403);
    }
}