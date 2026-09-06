<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    public function report(string $timeframe, int $year, int $month): array
    {
        [$startDate, $endDate] = $this->period($timeframe, $year, $month);

        $commission = Setting::get('platform_commission', 10);
        $completedSum = $this->completedQuery($startDate, $endDate)->sum('budget');
        $completedJobs = $this->completedQuery($startDate, $endDate)->count();

        $categoryDistribution = $this->completedQuery($startDate, $endDate)
            ->with('category')
            ->get()
            ->groupBy('category.name')
            ->filter(fn ($name) => $name !== null)
            ->mapWithKeys(fn ($tasks, $name) => [$name => $tasks->count()])
            ->all();

        $statusDistribution = Task::whereBetween('updated_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return [
            'timeframe' => $timeframe,
            'period_start' => $startDate->toDateString(),
            'period_end' => $endDate->toDateString(),
            'period_label' => $this->periodLabel($timeframe, $startDate),
            'total_transactions' => $completedSum,
            'platform_commission' => (int) round($completedSum * ((int) $commission / 100)),
            'commission_rate' => (int) $commission,
            'completed_jobs' => $completedJobs,
            'active_users' => User::where('is_active', true)->count(),
            'category_distribution' => $categoryDistribution,
            'trend' => $this->trend($timeframe, $startDate, $endDate),
            'task_status_distribution' => $statusDistribution,
        ];
    }

    private function completedQuery($startDate, $endDate)
    {
        return Task::where('status', 'completed')
            ->whereBetween('updated_at', [$startDate, $endDate]);
    }

    private function period(string $timeframe, int $year, int $month): array
    {
        if ($timeframe === 'weekly') {
            $start = Carbon::create($year, $month, 1)->startOfMonth();

            return [$start, $start->copy()->endOfMonth()];
        }

        if ($timeframe === 'yearly') {
            $start = Carbon::create($year, 1, 1)->startOfYear()->subYears(9);

            return [$start, $start->copy()->addYears(9)->endOfYear()];
        }

        $start = Carbon::create($year, 1, 1)->startOfYear();

        return [$start, $start->copy()->endOfYear()];
    }

    private function periodLabel(string $timeframe, Carbon $start): string
    {
        return match ($timeframe) {
            'weekly' => $start->locale('id')->translatedFormat('F Y'),
            'yearly' => $start->year . ' - ' . $start->copy()->addYears(9)->year,
            default => 'Tahun ' . $start->year,
        };
    }

    private function trend(string $timeframe, Carbon $startDate, Carbon $endDate): array
    {
        $concept = match ($timeframe) {
            'weekly' => 'week_4',
            'yearly' => 'year',
            default => 'month',
        };

        $rows = $this->completedQuery($startDate, $endDate)
            ->selectRaw("{$this->dbExpr($concept)} as bucket, SUM(budget) as total")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket');

        if ($timeframe === 'weekly') {
            $result = [];
            for ($w = 1; $w <= 4; $w++) {
                $result[] = ['label' => "Pekan {$w}", 'total' => (int) ($rows[(string) $w] ?? 0)];
            }

            return $result;
        }

        if ($timeframe === 'yearly') {
            $result = [];
            for ($i = 0; $i < 10; $i++) {
                $label = (string) $startDate->copy()->addYears($i)->year;
                $result[] = ['label' => $label, 'total' => (int) ($rows[$label] ?? 0)];
            }

            return $result;
        }

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $result = [];
        foreach ($months as $i => $name) {
            $key = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $result[] = ['label' => $name, 'total' => (int) ($rows[$key] ?? 0)];
        }

        return $result;
    }

    private function dbExpr(string $concept): string
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        return match ($concept) {
            'month' => $isSqlite ? "strftime('%m', updated_at)" : "DATE_FORMAT(updated_at, '%m')",
            'year' => $isSqlite ? "strftime('%Y', updated_at)" : "DATE_FORMAT(updated_at, '%Y')",
            'week_4' => $isSqlite
                ? "MIN(4, ((CAST(strftime('%d', updated_at) AS INTEGER) - 1) / 7) + 1)"
                : "LEAST(4, FLOOR((DAY(updated_at) - 1) / 7) + 1)",
            default => '',
        };
    }
}