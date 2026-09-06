<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function download(Request $request, AdminAnalyticsService $analytics)
    {
        $timeframe = $request->get('timeframe', 'monthly');
        $report = $analytics->report(
            $timeframe,
            (int) $request->get('year', now()->year),
            (int) $request->get('month', now()->month),
        );

        $logoPath = app()->basePath('../frontend/static/images/kerjain.webp');
        $logoBase64 = is_file($logoPath)
            ? base64_encode((string) file_get_contents($logoPath))
            : '';

        $html = view('reports.admin-report', [
            'logoBase64' => $logoBase64,
            'report' => $report,
            'timeframeLabel' => match ($timeframe) {
                'weekly' => 'Laporan Mingguan',
                'yearly' => 'Laporan Tahunan',
                default => 'Laporan Bulanan',
            },
            'generatedAt' => now()->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i'),
            'generatedBy' => $request->user()->name,
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

        $stamp = $timeframe === 'weekly'
            ? (int) $request->get('month', now()->month)
            : ((int) $request->get('year', now()->year));

        $suffix = $timeframe === 'weekly'
            ? sprintf('%04d-%02d', (int) $request->get('year', now()->year), $stamp)
            : (string) $stamp;

        $filename = 'Laporan-' . ucfirst($timeframe) . '-' . $suffix . '.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=' . str_replace(' ', '-', $filename),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }
}