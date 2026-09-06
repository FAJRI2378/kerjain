{{-- Laporan Admin KERJAIN — dirender server-side via dompdf --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Analytics - KERJAIN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.45;
            margin: 0;
        }
        .sheet { padding: 30px 36px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0d9488; padding-bottom: 14px; margin-bottom: 18px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo { width: 46px; height: 46px; border-radius: 10px; overflow: hidden; }
        .brand-logo img { width: 46px; height: 46px; object-fit: cover; display: block; }
        .brand-name { font-size: 19px; font-weight: 800; color: #0d233a; letter-spacing: 0.5px; }
        .brand-tagline { font-size: 8.5px; color: #64748b; margin-top: 2px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 20px; margin: 0 0 4px; color: #0d233a; font-weight: 800; }
        .doc-title .period { font-size: 11px; color: #0d9488; font-weight: 700; }

        .meta { margin-bottom: 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px; }
        .meta-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 2px 0; vertical-align: top; font-size: 9.5px; }
        .meta-table .k { color: #64748b; font-weight: 600; width: 150px; }
        .meta-table .v { font-weight: 700; color: #0f172a; }

        .section-title { font-size: 12px; font-weight: 800; color: #0d233a; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.06em; border-left: 4px solid #0d9488; padding-left: 8px; }

        .kpis { width: 100%; border-collapse: separate; border-spacing: 8px; margin: -8px -8px 12px; }
        .kpis td { width: 25%; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 10px 12px; }
        .kpi-label { display: block; font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 4px; }
        .kpi-value { display: block; font-size: 14px; font-weight: 800; color: #0f172a; }
        .kpi-sub { display: block; font-size: 7.5px; color: #64748b; margin-top: 3px; }

        .bar-row { margin-bottom: 10px; }
        .bar-head { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 9px; }
        .bar-head .lbl { font-weight: 700; color: #0f172a; }
        .bar-head .val { font-weight: 700; color: #0d9488; }
        .bar-track { width: 100%; height: 10px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .bar-fill { height: 100%; background: #0d9488; border-radius: 6px; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.data th, table.data td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; font-size: 9px; }
        table.data th { background: #f0fdfa; color: #0d233a; font-weight: 800; text-transform: uppercase; font-size: 8px; letter-spacing: 0.04em; }
        table.data td.right, table.data th.right { text-align: right; }

        .status-chip { font-weight: 700; }
        .status-pending { color: #b45309; }
        .status-approved { color: #0d9488; }
        .status-rejected { color: #b91c1c; }
        .status-in_progress { color: #1d4ed8; }
        .status-reviewing { color: #b45309; }
        .status-completed { color: #15803d; }
        .status-cancelled { color: #475569; }

        .footer { margin-top: 26px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0 0 3px; font-size: 8px; color: #64748b; }
        .sections-2 { width: 100%; border-spacing: 16px 0; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="top">
            <div class="brand">
                <div class="brand-logo">
                    <img src="data:image/webp;base64,{{ $logoBase64 }}" alt="KERJAIN">
                </div>
                <div>
                    <div class="brand-name">KERJAIN</div>
                    <div class="brand-tagline">Marketplace Pekerjaan Mikro Lokal</div>
                </div>
            </div>
            <div class="doc-title">
                <h1>{{ $timeframeLabel }}</h1>
                <div class="period">Periode: {{ $report['period_label'] }}</div>
            </div>
        </div>

        <div class="meta">
            <table class="meta-table">
                <tr>
                    <td class="k">Periode Data</td>
                    <td class="v">{{ $report['period_start'] }} s/d {{ $report['period_end'] }}</td>
                    <td class="k">Menampilkan</td>
                    <td class="v">{{ $timeframeLabel }}</td>
                </tr>
                <tr>
                    <td class="k">Diterbitkan</td>
                    <td class="v">{{ $generatedAt }}</td>
                    <td class="k">Oleh</td>
                    <td class="v">{{ $generatedBy }}</td>
                </tr>
            </table>
        </div>

        <h3 class="section-title">Ringkasan Kinerja Platform</h3>
        <table class="kpis">
            <tr>
                <td>
                    <span class="kpi-label">Total Perputaran (GMV)</span>
                    <span class="kpi-value">Rp{{ number_format($report['total_transactions'], 0, ',', '.') }}</span>
                    <span class="kpi-sub">Nilai transaksi selesai</span>
                </td>
                <td>
                    <span class="kpi-label">Pendapatan Platform</span>
                    <span class="kpi-value">Rp{{ number_format($report['platform_commission'], 0, ',', '.') }}</span>
                    <span class="kpi-sub">Komisi {{ $report['commission_rate'] }}%</span>
                </td>
                <td>
                    <span class="kpi-label">Tugas Selesai</span>
                    <span class="kpi-value">{{ number_format($report['completed_jobs'], 0, ',', '.') }} tugas</span>
                    <span class="kpi-sub">Pekerjaan selesai periode</span>
                </td>
                <td>
                    <span class="kpi-label">Pengguna Aktif</span>
                    <span class="kpi-value">{{ number_format($report['active_users'], 0, ',', '.') }} user</span>
                    <span class="kpi-sub">Freelancer &amp; UMKM</span>
                </td>
            </tr>
        </table>

        <h3 class="section-title">Tren Transaksi {{ $timeframeLabel }}</h3>
        @php
            $trendMax = max(1, ...collect($report['trend'])->pluck('total')->all());
        @endphp
        <div>
            @foreach ($report['trend'] as $t)
                <div class="bar-row">
                    <div class="bar-head">
                        <span class="lbl">{{ $t['label'] }}</span>
                        <span class="val">Rp{{ number_format($t['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill" style="width: {{ max(3, ($t['total'] / $trendMax) * 100) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <table class="sections-2">
            <tr>
                <td style="width:50%; vertical-align: top; padding-top: 14px;">
                    <h3 class="section-title">Distribusi Kategori</h3>
                    @php
                        $catTotal = max(1, array_sum($report['category_distribution']));
                    @endphp
                    @if (count($report['category_distribution']) === 0)
                        <p style="color:#64748b;">Belum ada data kategori pada periode ini.</p>
                    @else
                        @foreach ($report['category_distribution'] as $name => $count)
                            <div class="bar-row">
                                <div class="bar-head">
                                    <span class="lbl">{{ $name }}</span>
                                    <span class="val">{{ $count }} tugas ({{ round(($count / $catTotal) * 100) }}%)</span>
                                </div>
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ max(3, ($count / $catTotal) * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </td>
                <td style="width:50%; vertical-align: top; padding-top: 14px;">
                    <h3 class="section-title">Distribusi Status Tugas</h3>
                    @php
                        $statusLabels = [
                            'pending' => 'Menunggu Review',
                            'approved' => 'Aktif / Disetujui',
                            'rejected' => 'Ditolak',
                            'in_progress' => 'Sedang Dikerjakan',
                            'reviewing' => 'Menunggu Review Bukti',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $statusTotal = max(1, array_sum($report['task_status_distribution']));
                    @endphp
                    @if (count($report['task_status_distribution']) === 0)
                        <p style="color:#64748b;">Belum ada data tugas pada periode ini.</p>
                    @else
                        <div>
                            @foreach ($report['task_status_distribution'] as $status => $count)
                                <div class="bar-row">
                                    <div class="bar-head">
                                        <span class="lbl status-chip status-{{ $status }}">{{ $statusLabels[$status] ?? $status }}</span>
                                        <span class="val">{{ $count }} tugas ({{ round(($count / $statusTotal) * 100) }}%)</span>
                                    </div>
                                    <div class="bar-track">
                                        <div class="bar-fill" style="width: {{ max(3, ($count / $statusTotal) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="footer">
            <p>Laporan ini diterbitkan otomatis oleh sistem KERJAIN dan mencerminkan data aktivitas platform pada periode terpilih.</p>
            <p>Pembayaran dikelola melalui sistem escrow KERJAIN. Statistik dihitung dari tugas berstatus Selesai pada periode tersebut.</p>
            <p>KERJAIN — Marketplace Pekerjaan Mikro Lokal • dukungan@kerjain.id</p>
        </div>
    </div>
</body>
</html>