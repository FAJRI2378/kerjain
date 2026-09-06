<?php

namespace Tests\Feature\Auth;

use App\Models\BusinessProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_freelancer_can_update_profile(): void
    {
        $freelancer = User::factory()->freelancer()->create();

        $this->actingAs($freelancer, 'sanctum')
            ->post('/api/profile', [
                'name' => 'Andi Baru',
                'phone' => '081122334455',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('user.name', 'Andi Baru')
            ->assertJsonPath('user.phone', '081122334455');

        $this->assertDatabaseHas('users', ['id' => $freelancer->id, 'name' => 'Andi Baru']);
    }

    public function test_email_is_unique_ignoring_current_user(): void
    {
        $freelancer = User::factory()->freelancer()->create(['email' => 'andi@worker.id']);

        $this->actingAs($freelancer, 'sanctum')
            ->post('/api/profile', [
                'email' => 'andi@worker.id',
            ], ['Accept' => 'application/json'])
            ->assertOk();
    }

    public function test_hirer_can_update_business_profile(): void
    {
        Storage::fake('public');

        $hirer = User::factory()->hirer()->create();
        BusinessProfile::create([
            'user_id' => $hirer->id,
            'business_name' => 'Soto Ayam Pak Budi',
            'category' => 'Kuliner / F&B',
            'phone' => '0811111111',
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->post('/api/profile', [
                'name' => 'Budi Santoso',
                'phone' => '08122223333',
                'business_name' => 'Soto Ayam Pak Budi',
                'business_type' => 'Kuliner / F&B',
                'business_address' => 'Jl. Raya Cakung No. 1',
                'store_photo' => UploadedFile::fake()->image('store.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $this->assertDatabaseHas('business_profiles', [
            'user_id' => $hirer->id,
            'address' => 'Jl. Raya Cakung No. 1',
            'business_type' => 'Kuliner / F&B',
        ]);
        $this->assertDatabaseCount('verifications', 0);

        $storePhoto = $this->getConnection()
            ->table('business_profiles')
            ->where('user_id', $hirer->id)
            ->value('store_photo');

        Storage::disk('public')->assertExists($storePhoto);
    }

    public function test_hirer_avatar_photo_upload_is_stored(): void
    {
        Storage::fake('public');

        $freelancer = User::factory()->freelancer()->create();

        $response = $this->actingAs($freelancer, 'sanctum')
            ->post('/api/profile', [
                'name' => 'Andi',
                'photo' => UploadedFile::fake()->image('avatar.jpg'),
            ], ['Accept' => 'application/json'])
            ->assertOk();

        $avatar = $response->json('user.avatar');
        $avatarUrl = $response->json('user.avatar_url');

        $this->assertNotNull($avatar);
        $this->assertNotNull($avatarUrl);
        $this->assertStringContainsString('/storage/', $avatarUrl);
        $this->assertDatabaseHas('users', ['id' => $freelancer->id, 'avatar' => $avatar]);

        Storage::disk('public')->assertExists($avatar);
    }
}