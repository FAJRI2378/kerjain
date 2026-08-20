<script>
  let myTasks = $state([
    { id: 'JOB-901', title: 'Jasa Sebar Brosur 500 Lembar', hirer: 'UMKM Snack Sejahtera', budget: 'Rp 150.000', status: 'In Progress', proof: '' },
    { id: 'JOB-902', title: 'Foto Katalog 15 Produk Kuliner', hirer: 'Dapur Mama', budget: 'Rp 450.000', status: 'Reviewing', proof: 'https://link-drive-bukti-foto.com' }
  ]);

  function submitProof(id) {
    const proofUrl = prompt('Masukkan Link Bukti Pekerjaan (Drive / Imgur / Dokumen):');
    if (proofUrl) {
      myTasks = myTasks.map(t => t.id === id ? { ...t, status: 'Reviewing', proof: proofUrl } : t);
      alert('Bukti kerja berhasil dikirim ke Pemberi Kerja!');
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Tugas Saya</h1>
    <p class="text-xs md:text-sm text-slate-400">Pantau status pengerjaan tugas dan kirim bukti hasil kerja untuk pencairan dana.</p>
  </div>

  <!-- Tasks Table / Cards -->
  <div class="space-y-4">
    {#each myTasks as task (task.id)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] text-emerald-400 font-bold">{task.id}</span>
            {#if task.status === 'In Progress'}
              <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded text-[10px] font-bold">Pengerjaan</span>
            {:else if task.status === 'Reviewing'}
              <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded text-[10px] font-bold">Sedang Direview</span>
            {/if}
          </div>
          <h3 class="font-bold text-white text-base">{task.title}</h3>
          <p class="text-xs text-slate-400">UMKM: <span class="text-slate-200 font-medium">{task.hirer}</span></p>
        </div>

        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-slate-800">
          <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Bayaran</span>
            <span class="font-black text-emerald-400 text-sm">{task.budget}</span>
          </div>

          {#if task.status === 'In Progress'}
            <button 
              onclick={() => submitProof(task.id)}
              class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition"
            >
              Upload Bukti
            </button>
          {:else}
            <span class="text-xs text-slate-400 font-semibold bg-slate-950 px-3 py-2 rounded-xl border border-slate-800">
              ⏳ Menunggu Approvel UMKM
            </span>
          {/if}
        </div>
      </div>
    {/each}
  </div>
</div>