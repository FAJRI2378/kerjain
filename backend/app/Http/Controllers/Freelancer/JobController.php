<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\SubmitProofRequest;
use App\Http\Resources\TaskApplicationResource;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::where('status', 'approved')
            ->where('owner_id', '!=', $request->user()->id)
            ->with(['owner', 'category'])
            ->withCount('applicants');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%"));
            });
        }

        if ($category = $request->get('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        $tasks = $query->orderByDesc('created_at')->paginate($request->get('per_page', 20));

        return TaskResource::collection($tasks);
    }

    public function show(Request $request, Task $task)
    {
        $user = $request->user();

        $isAdmin = $user->role === 'admin';
        $isOwner = $task->owner_id === $user->id;
        $isWorker = $task->worker_id === $user->id;
        $hasApplied = $task->applications()->where('worker_id', $user->id)->exists();

        if (!$isAdmin && !$isOwner && !$isWorker && !$hasApplied && $task->status !== 'approved') {
            abort(404);
        }

        return new TaskResource($task->load(['owner', 'category', 'worker', 'invoice'])->loadCount('applicants'));
    }

    public function mine(Request $request)
    {
        $tasks = Task::where('worker_id', $request->user()->id)
            ->with(['owner', 'category', 'invoice'])
            ->orderByDesc('created_at')
            ->get();

        return TaskResource::collection($tasks);
    }

    public function apply(Task $task)
    {
        $user = request()->user();

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Only freelancers can apply to tasks.'], 403);
        }

        if (! $user->is_verified) {
            return response()->json([
                'message' => 'Verifikasi akun Anda terlebih dahulu untuk melamar pekerjaan.',
                'errors' => ['verification' => ['Akun Anda belum terverifikasi. Silakan selesaikan verifikasi identitas terlebih dahulu.']],
            ], 403);
        }

        if (! $user->phone) {
            return response()->json([
                'message' => 'Lengkapi nomor WhatsApp di profil sebelum melamar.',
                'errors' => ['phone' => ['Nomor WhatsApp wajib diisi pada profil terlebih dahulu.']],
            ], 422);
        }

        if ($task->owner_id === $user->id) {
            return response()->json(['message' => 'You cannot apply to your own task.'], 403);
        }

        if ($task->status !== 'approved') {
            return response()->json(['message' => 'This task is not open for applications.'], 409);
        }

        $existing = $task->applications()->where('worker_id', $user->id)->first();
        if ($existing) {
            return response()->json(['message' => 'You have already applied to this task.'], 409);
        }

        $application = $task->applications()->create([
            'worker_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Application submitted.',
            'data' => new TaskApplicationResource($application->load('worker')),
        ], 201);
    }

    public function submit(SubmitProofRequest $request, Task $task)
    {
        if ($task->worker_id !== $request->user()->id) {
            return response()->json(['message' => 'You are not assigned to this task.'], 403);
        }

        if ($task->status !== 'in_progress') {
            return response()->json(['message' => 'This task is not in progress.'], 409);
        }

        $proofImage = null;
        if ($request->hasFile('proof')) {
            $proofImage = $request->file('proof')->store('proofs/' . $task->id, 'public');
        }

        $task->update([
            'proof_url' => $request->filled('proof_url') ? $request->proof_url : null,
            'proof_image' => $proofImage,
            'revision_note' => null,
            'status' => 'reviewing',
        ]);

        return new TaskResource($task->fresh(['owner', 'category', 'worker', 'invoice']));
    }

    public function proofImage(Request $request, Task $task)
    {
        $user = $request->user();

        $isAdmin = $user->role === 'admin';
        $isOwner = $task->owner_id === $user->id;
        $isWorker = $task->worker_id === $user->id;
        $hasApplied = $task->applications()->where('worker_id', $user->id)->exists();

        if (!$isAdmin && !$isOwner && !$isWorker && !$hasApplied && $task->status !== 'approved') {
            abort(404);
        }

        if (!$task->proof_image || !Storage::disk('public')->exists($task->proof_image)) {
            abort(404);
        }

        return Storage::disk('public')->response($task->proof_image);
    }
}
