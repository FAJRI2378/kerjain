<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function freelancer(): User
    {
        return User::factory()->freelancer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Desain & Kreatif', 'slug' => 'desain-kreatif']);
    }

    private function completedInvoice(User $hirer, User $worker): Invoice
    {
        $task = Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $this->category()->id,
            'budget' => 150000,
        ]);

        return Invoice::create([
            'user_id' => $hirer->id,
            'task_id' => $task->id,
            'invoice_number' => 'INV-2026-001',
            'amount' => 150000,
            'status' => Invoice::STATUS_PAID,
            'issued_at' => now(),
        ]);
    }

    public function test_owner_can_download_invoice_pdf(): void
    {
        $hirer = $this->hirer();
        $invoice = $this->completedInvoice($hirer, $this->freelancer());

        $response = $this->actingAs($hirer, 'sanctum')
            ->get("/api/invoices/{$invoice->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=INV-2026-001.pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_assigned_worker_can_download_receipt_pdf(): void
    {
        $worker = $this->freelancer();
        $invoice = $this->completedInvoice($this->hirer(), $worker);

        $response = $this->actingAs($worker, 'sanctum')
            ->get("/api/invoices/{$invoice->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=INV-2026-001.pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_receipt_view_labels_for_worker(): void
    {
        $worker = $this->freelancer();
        $invoice = $this->completedInvoice($this->hirer(), $worker);

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'task' => $invoice->task->loadMissing(['owner', 'worker', 'category']),
            'isRecipientView' => true,
            'logoBase64' => '',
        ])->render();

        $this->assertStringContainsString('KUITANSI PEMBAYARAN', $html);
        $this->assertStringNotContainsString('INVOICE', $html);
    }

    public function test_invoice_view_labels_for_owner(): void
    {
        $hirer = $this->hirer();
        $invoice = $this->completedInvoice($hirer, $this->freelancer());

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'task' => $invoice->task->loadMissing(['owner', 'worker', 'category']),
            'isRecipientView' => false,
            'logoBase64' => '',
        ])->render();

        $this->assertStringContainsString('INVOICE', $html);
        $this->assertStringNotContainsString('KUITANSI PEMBAYARAN', $html);
    }

    public function test_unrelated_user_cannot_download(): void
    {
        $invoice = $this->completedInvoice($this->hirer(), $this->freelancer());

        $this->actingAs($this->freelancer(), 'sanctum')
            ->get("/api/invoices/{$invoice->id}/pdf")
            ->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_download(): void
    {
        $invoice = $this->completedInvoice($this->hirer(), $this->freelancer());

        $this->getJson("/api/invoices/{$invoice->id}/pdf")
            ->assertStatus(401);
    }
}