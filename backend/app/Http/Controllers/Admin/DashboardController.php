<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::count();
        $activeTasks = Task::whereIn('status', ['in_progress', 'reviewing'])->count();
        $pendingVerifications = User::where('is_verified', false)
            ->where('role', 'freelancer')
            ->count();
        $monthlyRevenue = Task::where('status', 'completed')
            ->whereMonth('updated_at', now()->month)
            ->sum('budget');

        $pendingJobs = Task::where('status', 'pending')
            ->with('owner')
            ->with('category')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => [
                'total_users' => $totalUsers,
                'active_tasks' => $activeTasks,
                'pending_verifications' => $pendingVerifications,
                'monthly_revenue' => $monthlyRevenue,
                'pending_jobs' => TaskResource::collection($pendingJobs),
            ],
        ]);
    }
}
