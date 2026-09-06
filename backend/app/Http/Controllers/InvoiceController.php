<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Request $request, Invoice $invoice)
    {
        $user = $request->user();
        $task = $invoice->task;

        if (! $task) {
            abort(404);
        }

        $isOwner = $invoice->user_id === $user->id;
        $isWorker = $task->worker_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (! $isOwner && ! $isWorker && ! $isAdmin) {
            abort(404);
        }

        $invoice->loadMissing(['task.owner', 'task.worker', 'task.category']);

        $logoPath = app()->basePath('../frontend/static/images/kerjain.webp');
        $logoBase64 = is_file($logoPath)
            ? base64_encode((string) file_get_contents($logoPath))
            : '';

        $html = view('invoices.pdf', [
            'invoice' => $invoice,
            'task' => $task->loadMissing(['owner', 'worker', 'category']),
            'isRecipientView' => (bool) $isWorker,
            'logoBase64' => $logoBase64,
        ])->render();

        $tempDir = storage_path('app/dompdf');
        @mkdir($tempDir, 0777, true);

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'tempDir' => $tempDir,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $invoice->invoice_number . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}