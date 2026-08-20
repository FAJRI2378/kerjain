<script>
  let searchQuery = $state('');
  let selectedFilter = $state('all'); // 'all', 'pending', 'approved', 'rejected'

  let jobs = $state([
    { id: 'JOB-901', title: 'Jasa Sebar Brosur 500 Lembar', author: 'UMKM Snack Sejahtera', category: 'Pemasaran', budget: 'Rp 150.000', status: 'pending', date: '2026-08-20' },
    { id: 'JOB-902', title: 'Foto Katalog 15 Produk Kuliner', author: 'Dapur Mama', category: 'Fotografi', budget: 'Rp 450.000', status: 'pending', date: '2026-08-20' },
    { id: 'JOB-903', title: 'Desain Logo & Banner Toko Olshop', author: 'Batik Nusantara', category: 'Desain Grafis', budget: 'Rp 300.000', status: 'approved', date: '2026-08-19' },
    { id: 'JOB-904', title: 'Input Data Transaksi Toko ke Excel', author: 'Toko Berkah Utama', category: 'Admin', budget: 'Rp 100.000', status: 'rejected', date: '2026-08-18' }
  ]);

  let filteredJobs = $derived(
    jobs.filter(job => {
      const matchSearch = job.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          job.author.toLowerCase().includes(searchQuery.toLowerCase());
      const matchFilter = selectedFilter === 'all' || job.status === selectedFilter;
      return matchSearch && matchFilter;
    })
  );

  function updateStatus(id, newStatus) {
    jobs = jobs.map(j => j.id === id ? { ...j, status: newStatus } : j);
  }

  function deleteJob(id) {
    if (confirm(`Yakin ingin menghapus ${id}?`)) {
      jobs = jobs.filter(j => j.id !== id);
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Management Jobs & Tugas</h1>
      <p class="text-xs md:text-sm text-slate-400">Moderasi dan tinjau semua postingan tugas dari pemberi kerja.</p>
    </div>
    <div class="text-xs font-semibold text-slate-400 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl">
      Total Tugas: <span class="text-purple-400 font-bold">{jobs.length}</span>
    </div>
  </div>

  <!-- Filter & Search Bar -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <input 
      type="text" 
      placeholder="Cari judul tugas atau nama UMKM..." 
      bind:value={searchQuery}
      class="w-full sm:w-80 px-4 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition"
    />

    <!-- Filter Buttons -->
    <div class="flex gap-1.5 bg-slate-900/80 p-1 border border-slate-800 rounded-xl text-xs w-full sm:w-auto overflow-x-auto">
      <button 
        onclick={() => selectedFilter = 'all'} 
        class={`px-3 py-1.5 rounded-lg transition ${selectedFilter === 'all' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Semua
      </button>
      <button 
        onclick={() => selectedFilter = 'pending'} 
        class={`px-3 py-1.5 rounded-lg transition ${selectedFilter === 'pending' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30 font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Pending
      </button>
      <button 
        onclick={() => selectedFilter = 'approved'} 
        class={`px-3 py-1.5 rounded-lg transition ${selectedFilter === 'approved' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Disetujui
      </button>
      <button 
        onclick={() => selectedFilter = 'rejected'} 
        class={`px-3 py-1.5 rounded-lg transition ${selectedFilter === 'rejected' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/30 font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Ditolak
      </button>
    </div>
  </div>

  <!-- Job List Table -->
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 bg-slate-950/50 text-slate-400 uppercase font-bold tracking-wider">
            <th class="p-4">ID & Judul</th>
            <th class="p-4">Pembuat</th>
            <th class="p-4">Kategori & Fee</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 text-slate-300">
          {#each filteredJobs as job (job.id)}
            <tr class="hover:bg-slate-800/30 transition">
              <td class="p-4 space-y-0.5">
                <span class="font-mono text-[10px] text-purple-400">{job.id}</span>
                <p class="font-bold text-white text-sm">{job.title}</p>
                <p class="text-[10px] text-slate-500">Tanggal: {job.date}</p>
              </td>
              <td class="p-4 font-medium text-slate-200">{job.author}</td>
              <td class="p-4 space-y-0.5">
                <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] font-semibold">{job.category}</span>
                <p class="font-bold text-emerald-400 mt-1">{job.budget}</p>
              </td>
              <td class="p-4">
                {#if job.status === 'pending'}
                  <span class="px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-lg font-bold text-[10px]">Pending</span>
                {:else if job.status === 'approved'}
                  <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg font-bold text-[10px]">Approved</span>
                {:else}
                  <span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg font-bold text-[10px]">Rejected</span>
                {/if}
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  {#if job.status !== 'approved'}
                    <button onclick={() => updateStatus(job.id, 'approved')} class="px-2.5 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold rounded-lg transition">Setujui</button>
                  {/if}
                  {#if job.status !== 'rejected'}
                    <button onclick={() => updateStatus(job.id, 'rejected')} class="px-2.5 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-bold rounded-lg transition">Tolak</button>
                  {/if}
                  <button onclick={() => deleteJob(job.id)} class="p-1.5 hover:bg-rose-500/20 text-rose-400 rounded-lg transition">🗑️</button>
                </div>
              </td>
            </tr>
          {:else}
            <tr>
              <td colspan="5" class="p-8 text-center text-slate-500">
                Tidak ada data tugas yang ditemukan.
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    </div>
  </div>
</div>