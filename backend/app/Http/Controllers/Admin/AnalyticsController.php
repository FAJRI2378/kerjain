<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $timeframe = $request->get('timeframe', 'monthly');
        $startDate = match ($timeframe) {
            'weekly' => now()->subWeek(),
            'yearly' => now()->subYear(),
            default => now()->subMonth(),
        };

        $completedJobs = Task::where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->count();

        $completedSum = Task::where('status', 'completed')
            ->where('updated_at', '>=', $startDate)
            ->sum('budget');

        $commission = Setting::get('platform_commission', 10);

        $platformCommission = (int) round($completedSum * ((int) $commission / 100));

        $activeUsers = User::where('is_active', true)->count();

        $categoryDistribution = Task::where('status', 'completed')
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->mapWithKeys(fn ($tasks, $name) => [$name => $tasks->count()]);

        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%m', updated_at)"
            : "DATE_FORMAT(updated_at, '%m')";

        $monthlyTrend = Task::where('status', 'completed')
            ->where('updated_at', '>=', now()->subMonths(6))
            ->selectRaw("{$monthExpression} as month, SUM(budget) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        return response()->json([
            'data' => [
                'total_transactions' => $completedSum,
                'platform_commission' => $platformCommission,
                'commission_rate' => (int) $commission,
                'completed_jobs' => $completedJobs,
                'active_users' => $activeUsers,
                'category_distribution' => $categoryDistribution,
                'monthly_trend' => $monthlyTrend,
                'timeframe' => $timeframe,
            ],
        ]);
    }
}
