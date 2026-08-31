<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let stats = $state({
    balance: 0,
    completedTasks: 0,
    activeTasks: 0,
    rating: null,
    totalReviews: 0
  });

  let activeJobs = $state([]);
  let loading = $state(true);

  onMount(async () => {
    try {
      const res = await api.get('/api/freelancer/dashboard');
      const d = res.data;
      stats = {
        balance: d.balance ?? 0,
        completedTasks: d.completed_tasks ?? 0,
        activeTasks: d.active_tasks ?? 0,
        rating: d.average_rating,
        totalReviews: d.total_reviews ?? 0
      };
      activeJobs = d.active_jobs ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat dashboard.'), 'error');
    } finally {
      loading = false;
    }
  });

  const statusLabel = $derived((status) => {
    return status === 'reviewing' ? 'Sedang Direview' : status === 'in_progress' ? 'In Progress' : status;
  });
</script>

<div class="p-6 md:p-10 space-y-8 font-sans">
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-semibold tracking-wider text-emerald-400 uppercase">Freelancer Portal</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1">Halo, {auth.user?.name ?? 'Freelancer'}! 👋</h1>
      <p class="text-xs md:text-sm text-slate-400">Siap menyelesaikan pekerjaan hari ini dan kumpulkan penghasilanmu?</p>
    </div>

    <a href="/freelancer/jobs" class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition text-center">
      🔍 Cari Tugas Baru
    </a>
  </div>

  {#if loading}
    <div class="text-center text-slate-400 text-sm py-10">Memuat dashboard...</div>
  {:else}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Saldo Siap Tarik</span>
        <span class="text-lg">💵</span>
      </div>
      <p class="text-2xl font-black text-emerald-400">{formatRupiah(stats.balance)}</p>
      <button class="w-full py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-bold text-[11px] rounded-lg transition">Tarik Dompet</button>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Tugas Aktif</span>
        <span class="text-lg">⚡</span>
      </div>
      <p class="text-3xl font-black text-white">{stats.activeTasks}</p>
      <span class="text-[10px] text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded font-medium">Dalam Pengerjaan</span>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Tugas Selesai</span>
        <span class="text-lg">✅</span>
      </div>
      <p class="text-3xl font-black text-white">{stats.completedTasks}</p>
      <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded font-medium">Total Berhasil</span>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Rating Pekerja</span>
        <span class="text-lg">⭐</span>
      </div>
      <p class="text-2xl font-black text-amber-400">{stats.rating ?? '-'} / 5.0</p>
      <span class="text-[10px] text-slate-400">Dari {stats.totalReviews} Ulasan UMKM</span>
    </div>
  </div>

  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-xl space-y-5">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="font-bold text-white text-base">Tugas Berjalan Saat Ini</h3>
        <p class="text-xs text-slate-400">Selesaikan dan upload bukti kerja sebelum tenggat waktu.</p>
      </div>
      <a href="/freelancer/mytasks" class="text-xs text-emerald-400 hover:underline font-semibold">Lihat Semua →</a>
    </div>

    {#if activeJobs.length === 0}
      <p class="text-sm text-slate-500 text-center py-6">Belum ada tugas berjalan. Cari tugas baru untuk mulai bekerja.</p>
    {:else}
    <div class="space-y-3">
      {#each activeJobs as job (job.id)}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl hover:border-slate-700 transition gap-4">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-emerald-400 font-bold">#{job.id}</span>
            <p class="font-bold text-white text-sm">{job.title}</p>
            <p class="text-xs text-slate-400">Pemberi Kerja: <span class="text-slate-200">{job.owner?.name ?? '-'}</span> • <span class="text-amber-400 font-medium">⏱️ {job.deadline ?? 'Tanpa tenggat'}</span> • <span class="text-slate-300">{statusLabel(job.status)}</span></p>
          </div>

          <div class="flex items-center justify-between sm:justify-end gap-4">
            <span class="font-bold text-emerald-400 text-sm">{formatRupiah(job.budget)}</span>
            <a href="/freelancer/mytasks" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl transition">Kelola Tugas</a>
          </div>
        </div>
      {/each}
    </div>
    {/if}
  </div>
  {/if}
</div>
