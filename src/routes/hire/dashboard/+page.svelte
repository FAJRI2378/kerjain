<script>
  let stats = $state({
    escrowBalance: 'Rp 2.500.000',
    totalSpent: 'Rp 4.200.000',
    activeJobsCount: 3,
    applicantsCount: 12
  });

  let activeJobs = $state([
    { id: 'JOB-101', title: 'Jasa Sebar Brosur Promo Toko 500 Lembar', applicants: 5, status: 'In Progress', budget: 'Rp 150.000' },
    { id: 'JOB-105', title: 'Pembuatan Desain Spanduk Fleksi 3x1 Meter', applicants: 7, status: 'Reviewing Proof', budget: 'Rp 250.000' }
  ]);
</script>

<div class="p-6 md:p-10 space-y-8 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500 animate-pulse"></span>
        <span class="text-xs font-semibold tracking-wider text-indigo-400 uppercase">Business Dashboard</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1">UMKM Snack Sejahtera 🏪</h1>
      <p class="text-xs md:text-sm text-slate-400">Kelola pengerjaan proyek, alokasi dana escrow, dan review pelamar.</p>
    </div>

    <a href="/hire/jobs/create" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition text-center flex items-center justify-center gap-2">
      <span>➕</span> Buat Tugas Baru
    </a>
  </div>

  <!-- Stats Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Escrow Balance -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Saldo Escrow (Aman)</span>
        <span class="text-lg">🛡️</span>
      </div>
      <p class="text-2xl font-black text-indigo-400">{stats.escrowBalance}</p>
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
      <p class="text-2xl font-black text-slate-200">{stats.totalSpent}</p>
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
      {#each activeJobs as job (job.id)}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl hover:border-slate-700 transition gap-4">
          <div class="space-y-1">
            <span class="text-[10px] font-mono text-indigo-400 font-bold">{job.id}</span>
            <p class="font-bold text-white text-sm">{job.title}</p>
            <p class="text-xs text-slate-400">Pelamar: <span class="text-slate-200 font-bold">{job.applicants} Freelancer</span> • Budget: <span class="text-emerald-400 font-bold">{job.budget}</span></p>
          </div>

          <div class="flex items-center justify-between sm:justify-end gap-3">
            {#if job.status === 'Reviewing Proof'}
              <span class="px-2.5 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold rounded-lg animate-pulse">
                🔍 Perlu Di-Review
              </span>
            {:else}
              <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold rounded-lg">
                ⚡ Berjalan
              </span>
            {/if}
            <a href="/hire/jobs" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl transition">
              Detail
            </a>
          </div>
        </div>
      {/each}
    </div>
  </div>
</div>