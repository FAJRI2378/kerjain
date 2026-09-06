<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\TaskApplication;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = TaskApplication::where('worker_id', $request->user()->id)
            ->with(['task' => fn ($q) => $q->with(['owner', 'category'])])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $applications->map(fn (TaskApplication $application) => [
                'id' => $application->id,
                'applied_at' => $application->created_at?->toISOString(),
                'status' => $application->status,
                'job' => [
                    'id' => $application->task->id,
                    'title' => $application->task->title,
                    'employer' => $application->task->owner?->name,
                    'category' => $application->task->category?->name,
                    'reward' => $application->task->budget,
                    'location' => $application->task->location,
                    'status' => $application->task->status,
                    'revision_note' => $application->task->revision_note,
                ],
                'message' => $this->statusMessage($application->status),
            ]),
        ]);
    }

    public function cancel(Request $request, TaskApplication $application)
    {
        if ($application->worker_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only cancel your own applications.'], 403);
        }

        if ($application->status !== 'pending') {
            return response()->json(['message' => 'This application can no longer be cancelled.'], 409);
        }

        $application->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Lamaran berhasil dibatalkan.']);
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'accepted' => 'Selamat! Pemilik UMKM telah memilih Anda. Silakan mulai kerjakan tugas sesuai tenggat waktu.',
            'rejected' => 'Maaf, UMKM telah memilih talenta lain yang lebih sesuai dengan kebutuhan lokasi.',
            'cancelled' => 'Lamaran Anda telah dibatalkan.',
            default => 'Lamaran Anda sedang ditinjau oleh pemilik UMKM.',
        };
    }
}