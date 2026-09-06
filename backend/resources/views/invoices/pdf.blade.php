{{-- PDF Invoice / Kuitansi KERJAIN — dirender server-side via dompdf --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{!! e($invoice->invoice_number) !!} - {{ $isRecipientView ? 'Kuitansi Pembayaran' : 'Invoice' }} - KERJAIN</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        .sheet { padding: 36px 40px; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #0d9488; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo {
            width: 46px; height: 46px; border-radius: 10px; overflow: hidden;
        }
        .brand-logo img { width: 46px; height: 46px; object-fit: cover; display: block; }
        .brand-name { font-size: 20px; font-weight: 800; color: #0d233a; letter-spacing: 0.5px; }
        .brand-tagline { font-size: 9px; color: #64748b; margin-top: 2px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 22px; margin: 0; color: #0d233a; font-weight: 800; }
        .doc-title .no { font-size: 11px; color: #0d9488; font-weight: 700; margin-top: 4px; }

        .meta { margin-bottom: 22px; }
        .meta-table, .amount-table, .items-table { width: 100%; border-collapse: collapse; }
        .meta-table td { padding: 3px 0; vertical-align: top; }
        .meta-table .k { color: #64748b; font-weight: 600; width: 130px; }
        .meta-table .v { font-weight: 700; color: #0f172a; }

        .parties { display: flex; gap: 24px; margin-bottom: 24px; }
        .party { flex: 1; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; }
        .party h3 { margin: 0 0 8px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; font-weight: 800; }
        .party .name { font-size: 13px; font-weight: 800; color: #0f172a; }
        .party .detail { font-size: 10.5px; color: #475569; margin-top: 3px; }

        .items-table th, .items-table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        .items-table th { background: #f0fdfa; color: #0d233a; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        .items-table td.right, .items-table th.right { text-align: right; }

        .amount-table { margin-top: 20px; }
        .amount-table td { padding: 4px 0; }
        .amount-table .label { color: #64748b; font-weight: 600; }
        .amount-table .value { text-align: right; font-weight: 700; }
        .amount-table .total-row .label, .amount-table .total-row .value {
            font-size: 14px; font-weight: 800; color: #0d233a; border-top: 2px solid #0d9488; padding-top: 8px;
        }
        .pay-state { text-align: right; font-size: 10px; color: #166534; font-weight: 700; margin-top: 6px; }

        .footer { margin-top: 34px; padding-top: 14px; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0 0 4px; font-size: 9.5px; color: #64748b; }
        .status-stamp { text-align: center; margin-top: 26px; }
        .stamp {
            display: inline-block; font-size: 26px; font-weight: 900; color: #15803d;
            border: 4px solid #15803d; border-radius: 8px; padding: 4px 26px;
            opacity: 0.85;
        }
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
                <h1>{{ $isRecipientView ? 'KUITANSI PEMBAYARAN' : 'INVOICE' }}</h1>
                <div class="no">No. {{ e($invoice->invoice_number) }}</div>
            </div>
        </div>

        <table class="meta-table">
            <tr>
                <td class="k">Tanggal Terbit</td>
                <td class="v">{{ $invoice->issued_at?->locale('id')->translatedFormat('l, d M Y') }}</td>
            </tr>
            <tr>
                <td class="k">ID Tugas</td>
                <td class="v">#{{ $task->id }}</td>
            </tr>
            <tr>
                <td class="k">Status</td>
                <td class="v">Lunas (Escrow Released)</td>
            </tr>
            <tr>
                <td class="k">Metode</td>
                <td class="v">Dompet KERJAIN (Escrow)</td>
            </tr>
        </table>

        <div class="parties">
            <div class="party">
                <h3>Ditagihkan Kepada (UMKM)</h3>
                <div class="name">{!! e($task->owner?->name ?? '-') !!}</div>
                <div class="detail">{{ $task->owner?->phone ? 'WA: ' . $task->owner->phone : '' }}</div>
                <div class="detail">Lokasi Tugas: {!! e($task->location ?? '-') !!}</div>
            </div>
            <div class="party">
                <h3>{{ $isRecipientView ? 'Pembayaran Diterima Oleh (Freelancer)' : 'Pembayaran Untuk (Freelancer)' }}</h3>
                <div class="name">{!! e($task->worker?->name ?? '-') !!}</div>
                <div class="detail">{{ $task->worker?->phone ? 'WA: ' . $task->worker->phone : '' }}</div>
                <div class="detail">Kategori: {!! e($task->category?->name ?? '-') !!}</div>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Pekerjaan</th>
                    <th>Kategori</th>
                    <th>Tanggal Selesai</th>
                    <th class="right">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{!! e($task->title) !!}</strong><br>
                        <span style="color:#64748b;">ID Tugas: #{{ $task->id }}</span>
                    </td>
                    <td>{!! e($task->category?->name ?? '-') !!}</td>
                    <td>{{ $invoice->issued_at?->locale('id')->translatedFormat('d M Y') }}</td>
                    <td class="right">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="amount-table">
            <tr>
                <td class="label">Jasa {{ e($task->worker?->name ?? 'Freelancer') }}</td>
                <td class="value">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Biaya Layanan</td>
                <td class="value">Rp0</td>
            </tr>
            <tr class="total-row">
                <td class="label">{{ $isRecipientView ? 'Total Diterima' : 'Total Tagihan' }}</td>
                <td class="value">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="pay-state">&#10004; {{ $isRecipientView ? 'Pembayaran telah diterima penuh' : 'Pembayaran LUNAS via escrow KERJAIN' }}</div>

        <div class="status-stamp"><span class="stamp">LUNAS</span></div>

        <div class="footer">
            <p>Dokumen ini diterbitkan otomatis oleh KERJAIN saat pekerjaan dinyatakan selesai oleh kedua pihak.</p>
            <p>Pembayaran dikelola sepenuhnya melalui sistem escrow KERJAIN sehingga dokumen ini tidak memerlukan tanda tangan kedua belah pihak.</p>
            <p>Jika ada pertanyaan, hubungi kami: dukungan@kerjain.id • KERJAIN — Marketplace Pekerjaan Mikro Lokal.</p>
        </div>
    </div>
</body>
</html>