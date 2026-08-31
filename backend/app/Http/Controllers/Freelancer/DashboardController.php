<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $completedTasks = Task::where('worker_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $balance = Task::where('worker_id', $user->id)
            ->where('status', 'completed')
            ->sum('budget');

        $activeTasks = Task::where('worker_id', $user->id)
            ->whereIn('status', ['in_progress', 'reviewing'])
            ->count();

        $ratings = Rating::where('reviewee_id', $user->id);

        $averageRating = $ratings->avg('rating');
        $totalReviews = $ratings->count();

        $activeJobs = Task::where('worker_id', $user->id)
            ->whereIn('status', ['in_progress', 'reviewing'])
            ->with(['owner', 'category'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'balance' => $balance,
                'completed_tasks' => $completedTasks,
                'active_tasks' => $activeTasks,
                'average_rating' => $averageRating ? round($averageRating, 1) : null,
                'total_reviews' => $totalReviews,
                'active_jobs' => $activeJobs,
            ],
        ]);
    }
}
