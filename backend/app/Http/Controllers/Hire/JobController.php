<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\RevisionTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Invoice;
use App\Models\JobCategory;
use App\Models\Setting;
use App\Models\Task;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function myJobs(Request $request)
    {
        $tasks = Task::where('owner_id', $request->user()->id)
            ->with(['category', 'worker', 'invoice'])
            ->withCount('applicants')
            ->orderByDesc('created_at')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        if (! $request->user()->is_verified) {
            return response()->json([
                'message' => 'Verifikasi akun UMKM Anda terlebih dahulu untuk membuat lowongan.',
                'errors' => ['verification' => ['Akun UMKM Anda belum terverifikasi. Silakan selesaikan verifikasi terlebih dahulu.']],
            ], 403);
        }

        if (! $request->user()->phone) {
            return response()->json([
                'message' => 'Lengkapi nomor WhatsApp di profil sebelum membuat lowongan.',
                'errors' => ['phone' => ['Nomor WhatsApp wajib diisi pada profil terlebih dahulu.']],
            ], 422);
        }

        $category = JobCategory::where('slug', $request->category)->firstOrFail();

        $autoApprove = filter_var(Setting::get('auto_approve_jobs', false), FILTER_VALIDATE_BOOLEAN);

        $task = Task::create([
            'owner_id' => $request->user()->id,
            'category_id' => $category->id,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'location' => $request->location,
            'deadline' => $request->get('deadline'),
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        return (new TaskResource($task->load(['owner', 'category'])))->response()->setStatusCode(201);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if (in_array($task->status, ['in_progress', 'reviewing', 'completed'])) {
            return response()->json(['message' => 'Tugas yang sedang berjalan tidak dapat diubah.'], 409);
        }

        $category = JobCategory::where('slug', $request->category)->firstOrFail();

        $task->update([
            'category_id' => $category->id,
            'title' => $request->title,
            'description' => $request->description,
            'budget' => $request->budget,
            'location' => $request->location,
            'deadline' => $request->get('deadline'),
        ]);

        if ($task->status === 'rejected') {
            $task->update([
                'status' => 'pending',
                'rejection_reason' => null,
            ]);
        }

        return new TaskResource($task->fresh(['owner', 'category']));
    }

    public function destroy(Request $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if (in_array($task->status, ['in_progress', 'reviewing'])) {
            return response()->json(['message' => 'Tugas yang sedang berjalan tidak dapat dihapus.'], 409);
        }

        $task->delete();

        return response()->noContent();
    }

    public function revision(RevisionTaskRequest $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($task->status !== 'reviewing') {
            return response()->json(['message' => 'Tugas sedang tidak menunggu review.'], 409);
        }

        $task->update([
            'status' => 'in_progress',
            'proof_url' => null,
            'proof_image' => null,
            'revision_note' => $request->input('note'),
        ]);

        return new TaskResource($task->fresh(['owner', 'category', 'worker']));
    }

    public function complete(Request $request, Task $task)
    {
        if ($task->owner_id !== $request->user()->id) {
            return response()->json(['message' => 'You do not own this task.'], 403);
        }

        if ($task->status !== 'reviewing') {
            return response()->json(['message' => 'Task is not awaiting approval.'], 409);
        }

        if (!$task->worker_id) {
            return response()->json(['message' => 'Task has no assigned worker.'], 409);
        }

        DB::transaction(function () use ($task) {
            $hirerWallet = $this->lockWallet($task->owner_id);

            if ($hirerWallet->balance < $task->budget) {
                abort(409, 'Saldo escrow tidak mencukupi. Silakan top up terlebih dahulu.');
            }

            $task->update(['status' => 'completed']);

            $hirerWallet->decrement('balance', $task->budget);
            $hirerWallet->transactions()->create([
                'type' => WalletTransaction::TYPE_OUT,
                'title' => "Bayar Kontrak: {$task->title}",
                'amount' => $task->budget,
            ]);

            $workerWallet = Wallet::forUser($task->worker_id);
            $workerWallet->increment('balance', $task->budget);
            $workerWallet->transactions()->create([
                'type' => WalletTransaction::TYPE_IN,
                'title' => "Pembayaran Tugas: {$task->title}",
                'amount' => $task->budget,
            ]);

            Invoice::create([
                'user_id' => $task->owner_id,
                'task_id' => $task->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'amount' => $task->budget,
                'status' => Invoice::STATUS_PAID,
                'issued_at' => now(),
            ]);
        });

        return new TaskResource($task->fresh(['owner', 'category', 'worker', 'invoice']));
    }

    private function lockWallet(int $userId): Wallet
    {
        $wallet = Wallet::where('user_id', $userId)->lockForUpdate()->first();

        return $wallet ?? Wallet::create(['user_id' => $userId, 'balance' => 0]);
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "INV-{$year}-";
        $last = Invoice::where('invoice_number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        if (!$last) {
            return $prefix . '001';
        }

        return $prefix . str_pad((string) ((int) substr($last, -3) + 1), 3, '0', STR_PAD_LEFT);
    }
}
