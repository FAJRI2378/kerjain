<?php

namespace App\Http\Controllers\Hire;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskApplication;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeStatuses = ['in_progress', 'reviewing'];

        $escrowBalance = Task::where('owner_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->sum('budget');

        $totalSpent = Task::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->sum('budget');

        $activeJobsCount = Task::where('owner_id', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
            ->count();

        $applicantsCount = TaskApplication::whereHas('task', fn ($q) => $q->where('owner_id', $user->id))
            ->where('status', 'pending')
            ->count();

        $activeJobs = Task::where('owner_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->with('category')
            ->withCount('applicants')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'escrow_balance' => $escrowBalance,
                'total_spent' => $totalSpent,
                'active_jobs_count' => $activeJobsCount,
                'applicants_count' => $applicantsCount,
                'active_jobs' => TaskResource::collection($activeJobs),
            ],
        ]);
    }
}
