<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah, formatNumber } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let timeframe = $state('monthly');
  let loading = $state(true);

  let analyticsData = $state({
    totalTransactions: 0,
    platformCommission: 0,
    commissionRate: 0,
    completedJobs: 0,
    activeUsers: 0,
    categoryDistribution: {},
    monthlyTrend: {}
  });

  async function load() {
    loading = true;
    try {
      const res = await api.get(`/api/admin/analytics?timeframe=${timeframe}`);
      const d = res.data;
      analyticsData = {
        totalTransactions: d.total_transactions ?? 0,
        platformCommission: d.platform_commission ?? 0,
        commissionRate: d.commission_rate ?? 0,
        completedJobs: d.completed_jobs ?? 0,
        activeUsers: d.active_users ?? 0,
        categoryDistribution: d.category_distribution ?? {},
        monthlyTrend: d.monthly_trend ?? {}
      };
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat analytics.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  $effect(() => {
    if (!timeframe) return;
    load();
  });

  const catEntries = $derived(Object.entries(analyticsData.categoryDistribution).map(([name, count]) => ({ name, count })));

  const catTotal = $derived(catEntries.reduce((sum, c) => sum + Number(c.count || 0), 0));

  const trendEntries = $derived(
    Object.entries(analyticsData.monthlyTrend)
      .sort((a, b) => (a[0] > b[0] ? 1 : -1))
      .map(([month, total]) => ({ month, total: Number(total) }))
  );

  const trendMax = $derived(Math.max(1, ...trendEntries.map((t) => t.total)));

  const monthLabel = $derived((m) => {
    const idx = Number(m) - 1;
    return ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][idx] ?? m;
  });
</script>

<div class="p-6 md:p-10 space-y-8 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Laporan & Analytics</h1>
      <p class="text-xs md:text-sm text-slate-400">Pantau pertumbuhan pengguna, transaksi, dan statistik pendapatan platform.</p>
    </div>
    
    <!-- Timeframe Filter -->
    <div class="flex gap-1.5 bg-slate-900 p-1 border border-slate-800 rounded-xl text-xs">
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
  </div>

  <!-- Key Metrics Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Perputaran GMV</span>
      <p class="text-2xl font-black text-white">{loading ? '...' : formatRupiah(analyticsData.totalTransactions)}</p>
      <p class="text-[10px] text-emerald-400 font-semibold">Total nilai transaksi periode ini</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pendapatan Platform (Fee)</span>
      <p class="text-2xl font-black text-purple-400">{loading ? '...' : formatRupiah(analyticsData.platformCommission)}</p>
      <p class="text-[10px] text-purple-300 font-semibold">Take-rate komisi {analyticsData.commissionRate}%</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas Selesai</span>
      <p class="text-2xl font-black text-white">{loading ? '...' : formatNumber(analyticsData.completedJobs)} Tugas</p>
      <p class="text-[10px] text-emerald-400 font-semibold">Total tugas selesai</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengguna Aktif</span>
      <p class="text-2xl font-black text-white">{loading ? '...' : formatNumber(analyticsData.activeUsers)}</p>
      <p class="text-[10px] text-blue-400 font-semibold">Freelancer & UMKM</p>
    </div>
  </div>

  <!-- Performance Charts Visual Placeholders -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart 1: Transaksi -->
    <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="font-bold text-white text-sm">Tren Transaksi & Revenue</h3>
          <p class="text-xs text-slate-400">Volume pekerjaan yang berhasil diselesaikan per bulan.</p>
        </div>
      </div>
      <!-- Dynamic Bar Chart -->
      <div class="h-48 flex items-end justify-between gap-2 pt-6 border-b border-slate-800 px-2">
        {#if loading}
          <div class="w-full text-center text-slate-500 text-xs">Memuat...</div>
        {:else}
          {#each trendEntries as t (t.month)}
            <div class="w-full flex flex-col items-center gap-1 group relative">
              <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t transition" style="height: {Math.max(3, (t.total / trendMax) * 100)}%;"></div>
              <span class="text-[10px] text-slate-400">{monthLabel(t.month)}</span>
            </div>
          {/each}
        {/if}
      </div>
      <div class="flex justify-between text-[10px] text-slate-500">
        <span>Trend volume transaksi ({timeframe})</span>
      </div>
    </div>

    <!-- Chart 2: Distribusi Kategori -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <h3 class="font-bold text-white text-sm">Distribusi Kategori Tugas</h3>
      <p class="text-xs text-slate-400">Kategori paling diminati UMKM.</p>
      
      <div class="space-y-3 pt-2 text-xs">
        {#if loading}
          <p class="text-slate-500">Memuat...</p>
        {:else if catTotal === 0}
          <p class="text-slate-500">Belum ada data kategori.</p>
        {:else}
          {#each catEntries as { name, count }, i (name)}
            <div>
              <div class="flex justify-between text-slate-300 mb-1">
                <span>{name}</span>
                <span class="font-bold text-purple-400">{Math.round((Number(count) / catTotal) * 100)}%</span>
              </div>
              <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="bg-purple-500 h-full rounded-full" style="width: {Math.max(2, (Number(count) / catTotal) * 100)}%;"></div>
              </div>
            </div>
          {/each}
        {/if}
      </div>
    </div>
  </div>
</div>