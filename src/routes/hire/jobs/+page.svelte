<script>
  let jobs = $state([
    { 
      id: 'JOB-101', 
      title: 'Jasa Sebar Brosur Promo Toko 500 Lembar', 
      budget: 'Rp 150.000', 
      status: 'In Progress', 
      freelancer: 'Budi Santoso', 
      proof: '' 
    },
    { 
      id: 'JOB-105', 
      title: 'Pembuatan Desain Spanduk Fleksi 3x1 Meter', 
      budget: 'Rp 250.000', 
      status: 'Reviewing Proof', 
      freelancer: 'Siti Rahma', 
      proof: 'https://link-desain-bukti.com/spanduk.png' 
    }
  ]);

  function approveWork(id) {
    if (confirm('Apakah Anda yakin menyetujui hasil pengerjaan ini? Dana Escrow akan ditransfer ke Freelancer.')) {
      jobs = jobs.map(j => j.id === id ? { ...j, status: 'Completed' } : j);
      alert('Pekerjaan Selesai! Dana berhasil dicairkan ke Freelancer.');
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Kelola Pekerjaan Saya</h1>
    <p class="text-xs md:text-sm text-slate-400">Review bukti pengerjaan tugas dari freelancer dan konfirmasi pencairan dana.</p>
  </div>

  <!-- List Jobs -->
  <div class="space-y-4">
    {#each jobs as job (job.id)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] text-indigo-400 font-bold">{job.id}</span>
            {#if job.status === 'Reviewing Proof'}
              <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded text-[10px] font-bold">Butuh Persetujuan</span>
            {:else if job.status === 'In Progress'}
              <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded text-[10px] font-bold">Sedang Dikerjakan</span>
            {:else}
              <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded text-[10px] font-bold">Selesai & Lunas</span>
            {/if}
          </div>
          <h3 class="font-bold text-white text-base">{job.title}</h3>
          <p class="text-xs text-slate-400">Freelancer: <span class="text-slate-200 font-medium">{job.freelancer}</span></p>
          {#if job.proof}
            <a href={job.proof} target="_blank" class="text-[11px] text-indigo-400 underline font-medium block pt-1">
              📎 Lihat Bukti Pekerjaan Freelancer
            </a>
          {/if}
        </div>

        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-slate-800">
          <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Nilai Kontrak</span>
            <span class="font-black text-indigo-400 text-sm">{job.budget}</span>
          </div>

          {#if job.status === 'Reviewing Proof'}
            <button 
              onclick={() => approveWork(job.id)}
              class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition"
            >
              Approve & Cairkan
            </button>
          {:else if job.status === 'In Progress'}
            <span class="text-xs text-slate-500 font-medium italic">Menunggu Upload Bukti</span>
          {:else}
            <span class="text-xs text-emerald-400 font-bold">✓ Selesai</span>
          {/if}
        </div>
      </div>
    {/each}
  </div>
</div>