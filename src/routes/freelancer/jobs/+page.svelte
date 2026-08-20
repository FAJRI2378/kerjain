<script>
  let searchQuery = $state('');
  let selectedCategory = $state('All');

  let jobs = $state([
    { id: 'JOB-101', title: 'Jasa Sebar Brosur Promo Toko 500 Lembar', hirer: 'UMKM Snack Sejahtera', location: 'Jakarta Selatan', budget: 'Rp 150.000', category: 'Pemasaran', deadline: '3 Hari' },
    { id: 'JOB-102', title: 'Foto Katalog 15 Menu Makanan Resto', hirer: 'Dapur Mama', location: 'Bandung', budget: 'Rp 450.000', category: 'Fotografi', deadline: '2 Hari' },
    { id: 'JOB-103', title: 'Admin Live Streaming TikTok 2 Jam', hirer: 'Fashion Store ID', location: 'Remote / Online', budget: 'Rp 200.000', category: 'Live Host', deadline: 'Hari Ini' },
    { id: 'JOB-104', title: 'Input Data Nota Penjualan ke Excel', hirer: 'Toko Berkah Utama', location: 'Remote / Online', budget: 'Rp 100.000', category: 'Admin', deadline: '5 Hari' }
  ]);

  let filteredJobs = $derived(
    jobs.filter(job => {
      const matchSearch = job.title.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          job.hirer.toLowerCase().includes(searchQuery.toLowerCase());
      const matchCategory = selectedCategory === 'All' || job.category === selectedCategory;
      return matchSearch && matchCategory;
    })
  );

  function applyJob(jobTitle) {
    alert(`Berhasil mengajukan lamaran untuk tugas: "${jobTitle}". Menunggu konfirmasi UMKM.`);
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Eksplor Lowongan Tugas</h1>
    <p class="text-xs md:text-sm text-slate-400">Temukan tugas harian atau sampingan dari UMKM sekitar dan mulai hasilkan uang.</p>
  </div>

  <!-- Search & Category Filters -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <input 
      type="text" 
      placeholder="Cari kata kunci tugas atau nama usaha..." 
      bind:value={searchQuery}
      class="w-full sm:w-80 px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition"
    />

    <div class="flex gap-1.5 bg-slate-900/80 p-1 border border-slate-800 rounded-xl text-xs w-full sm:w-auto overflow-x-auto">
      {#each ['All', 'Pemasaran', 'Fotografi', 'Live Host', 'Admin'] as category}
        <button 
          onclick={() => selectedCategory = category}
          class={`px-3 py-1.5 rounded-lg transition whitespace-nowrap ${selectedCategory === category ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white'}`}
        >
          {category}
        </button>
      {/each}
    </div>
  </div>

  <!-- Jobs List Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {#each filteredJobs as job (job.id)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl hover:border-slate-700 transition flex flex-col justify-between space-y-4">
        <div class="space-y-2">
          <div class="flex justify-between items-start gap-2">
            <span class="px-2.5 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-md">
              {job.category}
            </span>
            <span class="text-[10px] text-slate-500 font-mono">📍 {job.location}</span>
          </div>

          <h3 class="font-bold text-white text-base leading-snug">{job.title}</h3>
          <p class="text-xs text-slate-400">Pemberi Kerja: <span class="text-slate-200 font-medium">{job.hirer}</span></p>
        </div>

        <div class="pt-3 border-t border-slate-800/60 flex items-center justify-between">
          <div>
            <span class="text-[10px] text-slate-500 block">Honor Pekerjaan</span>
            <span class="text-lg font-black text-emerald-400">{job.budget}</span>
          </div>

          <button 
            onclick={() => applyJob(job.title)}
            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition"
          >
            Lamar Tugas
          </button>
        </div>
      </div>
    {:else}
      <div class="col-span-full py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-2xl">
        Tidak ada pekerjaan yang sesuai dengan kriteria pencarian.
      </div>
    {/each}
  </div>
</div>