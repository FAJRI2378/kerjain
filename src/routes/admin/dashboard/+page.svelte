<script>
  let stats = $state({
    totalUsers: 1240,
    activeTasks: 86,
    pendingVerifications: 5,
    monthlyRevenue: 'Rp 14.500.000'
  });

  // Data mock moderasi yang dapat diinteraksi
  let pendingJobs = $state([
    { id: 901, title: 'Jasa Sebar Brosur 500 Lembar', author: 'UMKM Snack Sejahtera', category: 'Pemasaran', date: 'Baru saja' },
    { id: 902, title: 'Foto Katalog 15 Produk Kuliner', author: 'Dapur Mama', category: 'Fotografi', date: '10 menit lalu' },
    { id: 903, title: 'Input Data Transaksi Toko ke Excel', author: 'Toko Berkah Utama', category: 'Admin', date: '1 jam lalu' }
  ]);

  function handleApprove(id) {
    pendingJobs = pendingJobs.filter(job => job.id !== id);
  }

  function handleReject(id) {
    pendingJobs = pendingJobs.filter(job => job.id !== id);
  }
</script>

<div class="min-h-screen bg-slate-950 text-slate-100 p-6 md:p-10 space-y-8 font-sans">
  
  <!-- Top Navigation & Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
        <span class="text-xs font-semibold tracking-wider text-purple-400 uppercase">Admin Portal</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1">Control Center</h1>
      <p class="text-xs md:text-sm text-slate-400">Monitoring real-time aktivitas platform dan kelola verifikasi sistem.</p>
    </div>

    <div class="flex items-center gap-3">
      <span class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-xl flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        System Healthy
      </span>
      <button class="px-4 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl transition">
        Unduh Laporan
      </button>
    </div>
  </div>

  <!-- Key Metrics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Total Pengguna</span>
        <span class="text-lg">👥</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-white">{stats.totalUsers.toLocaleString('id-ID')}</span>
        <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-md">+12% /bln</span>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Tugas Berjalan</span>
        <span class="text-lg">⚡</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-white">{stats.activeTasks}</span>
        <span class="text-[10px] text-blue-400 font-semibold bg-blue-500/10 px-2 py-0.5 rounded-md">Aktif</span>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Antrean Verifikasi</span>
        <span class="text-lg">🪪</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-purple-400">{stats.pendingVerifications}</span>
        <span class="text-[10px] text-purple-300 font-semibold bg-purple-500/10 px-2 py-0.5 rounded-md">Perlu Review</span>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider">Volume Transaksi</span>
        <span class="text-lg">💰</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-xl font-black text-white">{stats.monthlyRevenue}</span>
        <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-md">+8%</span>
      </div>
    </div>
  </div>

  <!-- Moderation Queue Table Section -->
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-xl space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="font-bold text-white text-base">Antrean Moderasi Postingan</h3>
        <p class="text-xs text-slate-400">Review postingan baru dari UMKM sebelum ditayangkan ke publik.</p>
      </div>
      <span class="text-xs font-bold text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800 w-fit">
        Sisa Antrean: <span class="text-purple-400">{pendingJobs.length}</span>
      </span>
    </div>

    {#if pendingJobs.length === 0}
      <div class="py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-xl">
        🎉 Semua postingan telah direview! Tidak ada antrean tersisa.
      </div>
    {:else}
      <div class="space-y-3">
        {#each pendingJobs as job (job.id)}
          <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl hover:border-slate-700 transition gap-4">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded">
                  #{job.id}
                </span>
                <span class="text-xs text-slate-500 font-medium">{job.category}</span>
                <span class="text-xs text-slate-600">• {job.date}</span>
              </div>
              <p class="font-bold text-white text-sm">{job.title}</p>
              <p class="text-xs text-slate-400">Diposting oleh: <span class="text-slate-200 font-medium">{job.author}</span></p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 self-end md:self-center">
              <button 
                onclick={() => handleApprove(job.id)}
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition"
              >
                Approve
              </button>
              <button 
                onclick={() => handleReject(job.id)}
                class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold text-xs rounded-xl transition"
              >
                Reject
              </button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>

</div>