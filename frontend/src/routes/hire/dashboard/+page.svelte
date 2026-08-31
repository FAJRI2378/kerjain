<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let stats = $state({
    escrowBalance: 0,
    totalSpent: 0,
    activeJobsCount: 0,
    applicantsCount: 0
  });

  let activeJobs = $state([]);
  let loading = $state(true);

  onMount(async () => {
    try {
      const res = await api.get('/api/hire/dashboard');
      const d = res.data;
      stats = {
        escrowBalance: d.escrow_balance ?? 0,
        totalSpent: d.total_spent ?? 0,
        activeJobsCount: d.active_jobs_count ?? 0,
        applicantsCount: d.applicants_count ?? 0
      };
      activeJobs = d.active_jobs ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat dashboard.'), 'error');
    } finally {
      loading = false;
    }
  });

  const businessName = $derived(auth.user?.business_profile?.business_name ?? auth.user?.name ?? 'UMKM');
  const statusBadge = $derived((status) =>
    status === 'reviewing'
      ? { label: '🔍 Perlu Di-Review', cls: 'bg-amber-500/10 text-amber-400 border-amber-500/20 animate-pulse' }
      : { label: '⚡ Berjalan', cls: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }
  );
</script>

<div class="p-6 md:p-10 space-y-8 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
        <span class="text-xs font-semibold tracking-wider text-indigo-400 uppercase">Business Dashboard</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1">{businessName} 🏪</h1>
      <p class="text-xs md:text-sm text-slate-400">Kelola pengerjaan proyek, alokasi dana escrow, dan review pelamar.</p>
    </div>

    <a href="/hire/jobs/create" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition text-center flex items-center justify-center gap-2">
      <span>➕</span> Buat Tugas Baru
    </a>
  </div>

  {#if loading}
    <div class="text-center text-slate-400 text-sm py-10">Memuat dashboard...</div>
  {:else}
  <!-- Stats Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Escrow Balance -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Saldo Escrow (Aman)</span>
        <span class="text-lg">🛡️</span>
      </div>
      <p class="text-2xl font-black text-indigo-400">{formatRupiah(stats.escrowBalance)}</p>
      <button class="w-full py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 text-indigo-300 font-bold text-[11px] rounded-lg transition">
        Top Up Saldo
      </button>
    </div>

    <!-- Active Jobs -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Tugas Tayang</span>
        <span class="text-lg">📌</span>
      </div>
      <p class="text-3xl font-black text-white">{stats.activeJobsCount}</p>
      <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded font-medium">Menerima Pelamar</span>
    </div>

    <!-- Pending Applicants -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Total Pelamar</span>
        <span class="text-lg">👥</span>
      </div>
      <p class="text-3xl font-black text-white">{stats.applicantsCount}</p>
      <span class="text-[10px] text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded font-medium">Butuh Review</span>
    </div>

    <!-- Total Spent -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Total Pengeluaran</span>
        <span class="text-lg">📈</span>
      </div>
      <p class="text-2xl font-black text-slate-200">{formatRupiah(stats.totalSpent)}</p>
      <span class="text-[10px] text-slate-400">Akumulasi Proyek</span>
    </div>
  </div>

  <!-- Active Jobs Overview -->
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-xl space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-bold text-white text-base">Status Pengerjaan Tugas</h3>
        <p class="text-xs text-slate-400">Pantau progres pengerjaan freelancer dan setujui bukti hasil kerja.</p>
      </div>
      <a href="/hire/jobs" class="text-xs text-indigo-400 hover:underline font-semibold">Kelola Semua →</a>
    </div>

    <div class="space-y-3">
      {#if activeJobs.length === 0}
        <p class="text-sm text-slate-500 text-center py-6">Belum ada tugas berjalan. Buat tugas baru untuk mulai.</p>
      {:else}
      {#each activeJobs as job (job.id)}
        {@const badge = statusBadge(job.status)}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl hover:border-slate-700 transition gap-4">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-indigo-400 font-bold">#{job.id}</span>
            <p class="font-bold text-white text-sm">{job.title}</p>
            <p class="text-xs text-slate-400">Pelamar: <span class="text-slate-200 font-bold">{job.applicants_count ?? 0} Freelancer</span> • Budget: <span class="text-emerald-400 font-bold">{formatRupiah(job.budget)}</span></p>
          </div>

          <div class="flex items-center justify-between sm:justify-end gap-3">
            <span class={`px-2.5 py-1 border text-[10px] font-bold rounded-lg ${badge.cls}`}>
              {badge.label}
            </span>
            <a href="/hire/jobs" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl transition">
              Detail
            </a>
          </div>
        </div>
      {/each}
      {/if}
    </div>
  </div>
  {/if}
</div>