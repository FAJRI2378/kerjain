<script>
  import { api, getBlobUrl } from '$lib/api/client.js';
  import { formatRupiah, formatNumber } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';
  import Chart from '$lib/components/chartjs/Chart.svelte';

  let timeframe = $state('monthly');
  let loading = $state(true);

  const currentDate = new Date();
  let selectedYear = $state(currentDate.getFullYear());
  let selectedMonth = $state(currentDate.getMonth() + 1);

  const periodValue = $derived(`${selectedYear}-${String(selectedMonth).padStart(2, '0')}`);

  let analyticsData = $state({
    totalTransactions: 0,
    platformCommission: 0,
    commissionRate: 0,
    completedJobs: 0,
    activeUsers: 0,
    categoryDistribution: {},
    taskStatusDistribution: {},
    trend: [],
    periodLabel: ''
  });

  function periodQuery() {
    return new URLSearchParams({
      timeframe,
      month: String(selectedMonth),
      year: String(selectedYear)
    }).toString();
  }

  async function load() {
    loading = true;
    try {
      const res = await api.get(`/api/admin/analytics?${periodQuery()}`);
      const d = res.data;
      analyticsData = {
        totalTransactions: d.total_transactions ?? 0,
        platformCommission: d.platform_commission ?? 0,
        commissionRate: d.commission_rate ?? 0,
        completedJobs: d.completed_jobs ?? 0,
        activeUsers: d.active_users ?? 0,
        categoryDistribution: d.category_distribution ?? {},
        taskStatusDistribution: d.task_status_distribution ?? {},
        trend: d.trend ?? [],
        periodLabel: d.period_label ?? ''
      };
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat analytics.'), 'error');
    } finally {
      loading = false;
    }
  }

  $effect(() => {
    load();
  });

  const catEntries = $derived(
    Object.entries(analyticsData.categoryDistribution).map(([name, count]) => ({ name, count: Number(count || 0) }))
  );
  const catTotal = $derived(catEntries.reduce((sum, c) => sum + c.count, 0));

  const statusEntries = $derived(
    Object.entries(analyticsData.taskStatusDistribution).map(([status, count]) => ({
      status,
      count: Number(count || 0)
    }))
  );
  const statusTotal = $derived(statusEntries.reduce((sum, s) => sum + s.count, 0));

  const statusLabels = {
    pending: '⏳ Menunggu Review',
    approved: '✅ Aktif / Disetujui',
    rejected: '❌ Ditolak',
    in_progress: '🚧 Sedang Dikerjakan',
    reviewing: '🔍 Menunggu Review Bukti',
    completed: '✅ Selesai',
    cancelled: '🚫 Dibatalkan'
  };

  const statusColors = {
    pending: '#d97706',
    approved: '#0d9488',
    rejected: '#dc2626',
    in_progress: '#2563eb',
    reviewing: '#d97706',
    completed: '#16a34a',
    cancelled: '#64748b'
  };

  const trendEntries = $derived((analyticsData.trend ?? []).map((t) => ({ label: t.label, total: Number(t.total || 0) })));

  const trendDatasets = $derived([
    {
      label: 'Transaksi (Rp)',
      data: trendEntries.map((t) => t.total),
      backgroundColor: 'rgba(168, 85, 247, 0.75)',
      hoverBackgroundColor: 'rgba(192, 132, 252, 0.9)',
      borderColor: '#a855f7',
      borderWidth: 1,
      borderRadius: 6,
      maxBarThickness: 46
    }
  ]);

  const trendLabels = $derived(trendEntries.map((t) => t.label));

  const doughnutPalette = ['#a855f7', '#0d9488', '#f59e0b', '#3b82f6', '#ec4899', '#22c55e', '#6366f1'];

  const catDatasets = $derived([
    {
      label: 'Tugas',
      data: catEntries.map((c) => c.count),
      backgroundColor: catEntries.map((_, i) => doughnutPalette[i % doughnutPalette.length]),
      borderColor: '#0f172a',
      borderWidth: 2
    }
  ]);

  const catLabels = $derived(catEntries.map((c) => c.name));

  async function downloadReport() {
    try {
      const url = await getBlobUrl(`/api/admin/report?${periodQuery()}`);
      const stamp = timeframe === 'weekly'
        ? `${selectedYear}-${String(selectedMonth).padStart(2, '0')}`
        : String(selectedYear);
      const a = document.createElement('a');
      a.href = url;
      a.download = `Laporan-Kerjain-${stamp}.pdf`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 2000);
      toast('Laporan PDF sedang diunduh.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengunduh laporan PDF.'), 'error');
    }
  }

  const years = $derived(
    Array.from({ length: 17 }, (_, i) => currentDate.getFullYear() - 15 + i)
  );
</script>

<div class="admin-analytics-page p-6 md:p-10 space-y-8 font-sans">
  <!-- Header -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-800/80 pb-6 analytics-header">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight analytics-title">Laporan & Analytics</h1>
      <p class="text-xs md:text-sm text-slate-400 analytics-sub">Pantau pertumbuhan pengguna, transaksi, dan statistik pendapatan platform.</p>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
      <!-- Timeframe Filter -->
      <div class="flex gap-1.5 bg-slate-900 p-1 border border-slate-800 rounded-xl text-xs timeframe-group">
        <button
          onclick={() => timeframe = 'weekly'}
          class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'weekly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
        >
          Mingguan
        </button>
        <button
          onclick={() => timeframe = 'monthly'}
          class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'monthly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
        >
          Bulanan
        </button>
        <button
          onclick={() => timeframe = 'yearly'}
          class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'yearly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
        >
          Tahunan
        </button>
      </div>

      <!-- Date Picker + Download -->
      <div class="flex flex-wrap items-center gap-2 period-picker-group">
        {#if timeframe === 'weekly'}
          <input
            type="month"
            value={periodValue}
            onchange={(e) => {
              const v = e.currentTarget.value;
              if (!v) return;
              const [y, m] = v.split('-').map(Number);
              selectedYear = y;
              selectedMonth = m;
            }}
            class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold text-slate-300 outline-none focus:border-purple-500 period-input"
          />
        {:else}
          <select
            bind:value={selectedYear}
            class="px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs font-bold text-slate-300 outline-none focus:border-purple-500 period-input"
          >
            {#each years as y (y)}
              <option value={y}>{y}</option>
            {/each}
          </select>
        {/if}

        <button
          onclick={downloadReport}
          disabled={loading}
          class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl transition disabled:opacity-50 flex items-center gap-1.5 btn-report-download"
        >
          📥 Unduh Laporan PDF
        </button>
      </div>
    </div>
  </div>

  <!-- Period Info -->
  <div class="text-xs text-slate-400 analytics-sub period-caption">
    Periode aktif: <strong class="text-purple-400">{analyticsData.periodLabel || 'Memuat...'}</strong>
    {#if !loading}{`(${analyticsData.trend.length} titik data)`}{/if}
  </div>

  <!-- Key Metrics Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2 analytics-card">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 card-label">Total Perputaran GMV</span>
      <p class="text-2xl font-black text-white card-val">{loading ? '...' : formatRupiah(analyticsData.totalTransactions)}</p>
      <p class="text-[10px] text-emerald-400 font-semibold">Total nilai transaksi periode ini</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2 analytics-card">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 card-label">Pendapatan Platform (Fee)</span>
      <p class="text-2xl font-black text-purple-400 card-val-purple">{loading ? '...' : formatRupiah(analyticsData.platformCommission)}</p>
      <p class="text-[10px] text-purple-300 font-semibold">Take-rate komisi {analyticsData.commissionRate}%</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2 analytics-card">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 card-label">Tugas Selesai</span>
      <p class="text-2xl font-black text-white card-val">{loading ? '...' : `${formatNumber(analyticsData.completedJobs)} Tugas`}</p>
      <p class="text-[10px] text-emerald-400 font-semibold">Total tugas selesai</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2 analytics-card">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 card-label">Pengguna Aktif</span>
      <p class="text-2xl font-black text-white card-val">{loading ? '...' : formatNumber(analyticsData.activeUsers)}</p>
      <p class="text-[10px] text-blue-400 font-semibold">Freelancer & UMKM</p>
    </div>
  </div>

  <!-- Charts -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart 1: Trend Transaksi -->
    <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4 analytics-card">
      <div>
        <h3 class="font-bold text-white text-sm chart-title">Tren Transaksi & Revenue</h3>
        <p class="text-xs text-slate-400 chart-sub">
          {#if timeframe === 'weekly'}4 pekan dalam bulan terpilih.{:else if timeframe === 'yearly'}10 tahun terakhir.{:else}Tiap bulan dalam tahun terpilih.{/if}
        </p>
      </div>
      <div class="chart-holder">
        {#if loading}
          <div class="w-full text-center text-slate-500 text-xs py-16">Memuat...</div>
        {:else}
          <Chart type="bar" labels={trendLabels} datasets={trendDatasets} height={230} />
        {/if}
      </div>
    </div>

    <!-- Chart 2: Distribusi Kategori -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4 analytics-card">
      <h3 class="font-bold text-white text-sm chart-title">Distribusi Kategori Tugas</h3>
      <p class="text-xs text-slate-400 chart-sub">Kategori paling diminati UMKM.</p>
      <div class="chart-holder flex items-center justify-center">
        {#if loading}
          <p class="text-slate-500 text-xs">Memuat...</p>
        {:else if catTotal === 0}
          <p class="text-slate-500 text-xs">Belum ada data kategori.</p>
        {:else}
          <div class="w-full max-w-[260px]">
            <Chart type="doughnut" labels={catLabels} datasets={catDatasets} height={230} />
          </div>
        {/if}
      </div>
      {#if catEntries.length > 0}
        <div class="space-y-1.5 pt-1 text-xs">
          {#each catEntries as { name, count }, i (name)}
            <div class="flex justify-between items-center text-slate-300 cat-name-label">
              <span class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background: {doughnutPalette[i % doughnutPalette.length]};"></span>
                {name}
              </span>
              <span class="font-bold text-purple-400">{Math.round((count / catTotal) * 100)}%</span>
            </div>
          {/each}
        </div>
      {/if}
    </div>
  </div>

  <!-- Chart 3: Status Tugas -->
  <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4 analytics-card">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div>
        <h3 class="font-bold text-white text-sm chart-title">Distribusi Status Tugas</h3>
        <p class="text-xs text-slate-400 chart-sub">Seluruh status tugas pada periode terpilih.</p>
      </div>
      <span class="rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-300 text-[11px] font-bold px-3 py-1.5">
        {statusTotal} total tugas
      </span>
    </div>

    {#if loading}
      <p class="text-slate-500 text-xs">Memuat...</p>
    {:else if statusTotal === 0}
      <p class="text-slate-500 text-xs">Belum ada data tugas pada periode ini.</p>
    {:else}
      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 pt-2">
        {#each statusEntries as { status, count } (status)}
          <div>
            <div class="flex justify-between text-xs mb-1">
              <span class="font-bold cat-name-label">{statusLabels[status] ?? status}</span>
              <span class="font-bold" style="color: {statusColors[status] ?? '#a855f7'}">
                {count} <span class="text-slate-400 font-semibold">({Math.round((count / statusTotal) * 100)}%)</span>
              </span>
            </div>
            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden cat-bar-bg">
              <div class="h-full rounded-full" style="width: {Math.max(2, (count / statusTotal) * 100)}%; background: {statusColors[status] ?? '#a855f7'};"></div>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>

<style>
  /* Light Theme Adjustments for Admin Analytics Page */
  :global(body:not(.dark-theme)) .admin-analytics-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .analytics-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .analytics-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .analytics-sub,
  :global(body:not(.dark-theme)) .period-caption {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .timeframe-group,
  :global(body:not(.dark-theme)) .period-input {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .analytics-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  :global(body:not(.dark-theme)) .card-label {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .card-val,
  :global(body:not(.dark-theme)) .chart-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .chart-sub {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .cat-name-label {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .cat-bar-bg {
    background-color: #e2e8f0 !important;
  }

  .period-input::-webkit-calendar-picker-indicator {
    filter: invert(0.6);
    cursor: pointer;
  }
</style>