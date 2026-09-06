<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let searchQuery = $state('');
  let selectedFilter = $state('all');
  let jobs = $state([]);
  let total = $state(0);
  let loading = $state(true);

  async function load() {
    loading = true;
    try {
      const params = new URLSearchParams({ per_page: '50' });
      if (searchQuery) params.set('search', searchQuery);
      if (selectedFilter !== 'all') params.set('status', selectedFilter);
      const res = await api.get(`/api/admin/tasks?${params.toString()}`);
      jobs = res.data ?? [];
      total = res.meta?.total ?? jobs.length;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat data tugas.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  $effect(() => {
    if (!searchQuery && selectedFilter === 'all') return;
    const t = setTimeout(load, 300);
    return () => clearTimeout(t);
  });

  async function updateStatus(id, newStatus) {
    try {
      await api.post(`/api/admin/tasks/${id}/${newStatus === 'approved' ? 'approve' : 'reject'}`, {});
      toast(newStatus === 'approved' ? 'Tugas disetujui.' : 'Tugas ditolak.', 'success');
      await load();
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengubah status.'), 'error');
    }
  }

  async function deleteJob(id) {
    if (!confirm(`Yakin ingin menghapus #${id}?`)) return;
    try {
      await api.delete(`/api/admin/tasks/${id}`);
      toast('Tugas dihapus.', 'success');
      await load();
    } catch (err) {
      toast(errorMessage(err, 'Gagal menghapus.'), 'error');
    }
  }
</script>

<div class="admin-jobs-page p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6 jobs-header">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight jobs-title">Management Jobs & Tugas</h1>
      <p class="text-xs md:text-sm text-slate-400">Moderasi dan tinjau semua postingan tugas dari pemberi kerja.</p>
    </div>
    <div class="text-xs font-semibold text-slate-400 bg-slate-900 border border-slate-800 px-4 py-2 rounded-xl total-badge">
      Total Tugas: <span class="text-purple-400 font-bold">{total}</span>
    </div>
  </div>

  <!-- Filter & Search Bar -->
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <input 
      type="text" 
      placeholder="Cari judul tugas atau nama UMKM..." 
      bind:value={searchQuery}
      class="w-full sm:w-80 px-4 py-2.5 bg-slate-900/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 transition search-input"
    />

    <!-- Filter Buttons -->
    <div class="flex gap-1.5 bg-slate-900/80 p-1 border border-slate-800 rounded-xl text-xs w-full sm:w-auto overflow-x-auto filter-group">
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
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl table-container-box">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-800 bg-slate-950/50 text-slate-400 uppercase font-bold tracking-wider table-head-row">
            <th class="p-4">ID & Judul</th>
            <th class="p-4">Pembuat</th>
            <th class="p-4">Kategori & Fee</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 text-slate-300 table-body">
          {#if loading}
            <tr><td colspan="5" class="p-8 text-center text-slate-500">Memuat data...</td></tr>
          {:else}
          {#each jobs as job (job.id)}
            <tr class="hover:bg-slate-800/30 transition table-row-item">
              <td class="p-4 space-y-0.5">
                <span class="font-mono text-[10px] text-purple-400">#{job.id}</span>
                <p class="font-bold text-white text-sm job-title">{job.title}</p>
                {#if job.created_at}<p class="text-[10px] text-slate-500">Tanggal: {new Date(job.created_at).toLocaleDateString('id-ID')}</p>{/if}
              </td>
              <td class="p-4 font-medium text-slate-200 job-owner-name">{job.owner?.name ?? '-'}</td>
              <td class="p-4 space-y-0.5">
                <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] font-semibold category-badge">{job.category?.name ?? '-'}</span>
                <p class="font-bold text-emerald-400 mt-1">{formatRupiah(job.budget)}</p>
              </td>
              <td class="p-4">
                {#if job.status === 'pending'}
                  <span class="px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-lg font-bold text-[10px]">Pending</span>
                {:else if job.status === 'approved'}
                  <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg font-bold text-[10px]">Approved</span>
                {:else if job.status === 'rejected'}
                  <span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg font-bold text-[10px]">Rejected</span>
                {:else}
                  <span class="px-2.5 py-1 bg-slate-500/10 border border-slate-500/20 text-slate-300 rounded-lg font-bold text-[10px]">{job.status}</span>
                {/if}
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  {#if job.status === 'pending'}
                    <button onclick={() => updateStatus(job.id, 'approved')} class="px-2.5 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold rounded-lg transition">Setujui</button>
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
          {/if}
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
  /* Light Theme Adjustments for Admin Jobs Page */
  :global(body:not(.dark-theme)) .admin-jobs-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .jobs-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .jobs-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .total-badge {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .search-input {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .search-input::placeholder {
    color: #94a3b8 !important;
  }

  :global(body:not(.dark-theme)) .filter-group {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .table-container-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  :global(body:not(.dark-theme)) .table-head-row {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .table-body {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .table-row-item:hover {
    background-color: #f8fafc !important;
  }

  :global(body:not(.dark-theme)) .job-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .job-owner-name {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .category-badge {
    background-color: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
  }
</style>