<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('task.worker')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('issued_at')
            ->get();

        return response()->json([
            'data' => [
                'invoices' => $invoices->map(fn (Invoice $invoice) => [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'task_id' => $invoice->task_id,
                    'task' => $invoice->task?->title ?? 'Tugas',
                    'worker' => $invoice->task?->worker?->name ?? '-',
                    'amount' => $invoice->amount,
                    'date' => $invoice->issued_at?->translatedFormat('d M Y'),
                    'status' => 'Lunas (Escrow Released)',
                ]),
            ],
        ]);
    }
}