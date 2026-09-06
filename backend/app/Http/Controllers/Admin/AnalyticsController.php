<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request, AdminAnalyticsService $analytics)
    {
        $report = $analytics->report(
            $request->get('timeframe', 'monthly'),
            (int) $request->get('year', now()->year),
            (int) $request->get('month', now()->month),
        );

        return response()->json(['data' => $report]);
    }
}