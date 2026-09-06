<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_freelancer(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@worker.com',
            'password' => 'password123',
            'role' => 'freelancer',
            'phone' => '081234567890',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.name', 'Ahmad Fauzi')
            ->assertJsonPath('data.user.role', 'freelancer')
            ->assertJsonPath('data.user.phone', '081234567890')
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', ['email' => 'ahmad@worker.com']);
    }

    public function test_user_can_register_as_hirer_with_business_name(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Kopi Hits',
            'email' => 'kopi@umkm.com',
            'password' => 'password123',
            'role' => 'hirer',
            'phone' => '081298765432',
            'business_name' => 'Kopi Hits Nusantara',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.role', 'hirer')
            ->assertJsonPath('data.user.phone', '081298765432')
            ->assertJsonPath('data.user.business_profile.business_name', 'Kopi Hits Nusantara');

        $this->assertDatabaseHas('business_profiles', ['business_name' => 'Kopi Hits Nusantara']);
        $this->assertDatabaseHas('users', ['email' => 'kopi@umkm.com', 'phone' => '081298765432']);
    }

    public function test_register_requires_phone(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'John',
            'email' => 'john@mail.com',
            'password' => 'password123',
            'role' => 'freelancer',
        ])->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_register_with_existing_email_fails(): void
    {
        User::factory()->create(['email' => 'taken@mail.com']);

        $this->postJson('/api/auth/register', [
            'name' => 'John',
            'email' => 'taken@mail.com',
            'password' => 'password123',
            'role' => 'freelancer',
        ])->assertStatus(422);
    }

    public function test_register_with_short_password_fails(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'John',
            'email' => 'john@mail.com',
            'password' => 'short',
            'role' => 'freelancer',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->freelancer()->create([
            'email' => 'login@mail.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@mail.com',
            'password' => 'password123',
            'role' => 'freelancer',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'login@mail.com')
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $user = User::factory()->freelancer()->create([
            'email' => 'login@mail.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'login@mail.com',
            'password' => 'wrongpassword',
            'role' => 'freelancer',
        ])->assertStatus(422);
    }

    public function test_login_with_wrong_role_fails(): void
    {
        $user = User::factory()->freelancer()->create([
            'email' => 'login@mail.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'login@mail.com',
            'password' => 'password123',
            'role' => 'hirer',
        ])->assertStatus(403);
    }

    public function test_login_with_suspended_account_fails(): void
    {
        $user = User::factory()->freelancer()->suspended()->create([
            'email' => 'suspended@mail.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'suspended@mail.com',
            'password' => 'password123',
            'role' => 'freelancer',
        ])->assertStatus(403);
    }

    public function test_authenticated_user_can_get_self(): void
    {
        $user = User::factory()->freelancer()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_accessible_without_token_fails(): void
    {
        $this->getJson('/api/auth/user')->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->freelancer()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/logout')
            ->assertStatus(204);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
