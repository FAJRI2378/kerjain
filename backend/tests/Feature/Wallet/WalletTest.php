<?php

namespace Tests\Feature\Wallet;

use App\Models\Invoice;
use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function worker(): User
    {
        return User::factory()->freelancer()->create();
    }

    public function test_freelancer_wallet_returns_empty_balance(): void
    {
        $this->actingAs($this->worker(), 'sanctum')
            ->getJson('/api/freelancer/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 0)
            ->assertJsonPath('data.transactions', []);
    }

    public function test_freelancer_can_withdraw_funds(): void
    {
        $worker = $this->worker();
        Wallet::create(['user_id' => $worker->id, 'balance' => 500000]);

        $this->actingAs($worker, 'sanctum')
            ->postJson('/api/freelancer/wallet/withdraw', [
                'amount' => 200000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
            ])
            ->assertOk()
            ->assertJsonPath('data.balance', 300000);

        $this->assertDatabaseHas('wallets', ['user_id' => $worker->id, 'balance' => 300000]);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'out',
            'title' => 'Penarikan Dana ke BCA (1234567890)',
            'amount' => 200000,
        ]);
    }

    public function test_freelancer_cannot_withdraw_more_than_balance(): void
    {
        $worker = $this->worker();
        Wallet::create(['user_id' => $worker->id, 'balance' => 100000]);

        $this->actingAs($worker, 'sanctum')
            ->postJson('/api/freelancer/wallet/withdraw', [
                'amount' => 200000,
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
            ])
            ->assertStatus(409);

        $this->assertDatabaseHas('wallets', ['user_id' => $worker->id, 'balance' => 100000]);
        $this->assertDatabaseCount('wallet_transactions', 0);
    }

    public function test_withdraw_requires_valid_payload(): void
    {
        $this->actingAs($this->worker(), 'sanctum')
            ->postJson('/api/freelancer/wallet/withdraw', ['amount' => 0])
            ->assertStatus(422);
    }

    public function test_hirer_can_top_up_escrow(): void
    {
        $hirer = $this->hirer();

        $this->actingAs($hirer, 'sanctum')
            ->postJson('/api/hire/wallet/topup', [
                'amount' => 1500000,
                'bank_name' => 'BCA',
            ])
            ->assertOk()
            ->assertJsonPath('data.balance', 1500000);

        $this->assertDatabaseHas('wallets', ['user_id' => $hirer->id, 'balance' => 1500000]);
        $this->assertDatabaseHas('wallet_transactions', [
            'type' => 'topup',
            'title' => 'Top Up Escrow via Transfer BCA',
            'amount' => 1500000,
        ]);
    }

    public function test_hirer_sees_invoices_after_escrow_release(): void
    {
        $hirer = $this->hirer();
        $worker = $this->worker();
        $task = Task::factory()->reviewing()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => JobCategory::create(['name' => 'Desain', 'slug' => 'desain'])->id,
            'status' => 'completed',
            'budget' => 350000,
        ]);
        Invoice::create([
            'user_id' => $hirer->id,
            'task_id' => $task->id,
            'invoice_number' => 'INV-2026-001',
            'amount' => 350000,
            'status' => 'paid',
            'issued_at' => now(),
        ]);

        $this->actingAs($hirer, 'sanctum')
            ->getJson('/api/hire/invoices')
            ->assertOk()
            ->assertJsonPath('data.invoices.0.number', 'INV-2026-001')
            ->assertJsonPath('data.invoices.0.task', $task->title)
            ->assertJsonPath('data.invoices.0.amount', 350000);
    }
}