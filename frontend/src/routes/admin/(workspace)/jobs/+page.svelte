<script>
  import { onMount } from 'svelte';
  import { api, getBlobUrl } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let searchQuery = $state('');
  let selectedFilter = $state('all');
  let jobs = $state([]);
  let total = $state(0);
  let loading = $state(true);

  // State untuk Modal Alasan Penolakan
  let isRejectModalOpen = $state(false);
  let targetJobId = $state(null);
  let rejectionReason = $state('');
  let isSubmitting = $state(false);

  // State untuk Modal Detail Tugas
  let detailJob = $state(null);
  let isDetailModalOpen = $state(false);
  let loadingDetail = $state(false);
  let detailProofBlob = $state(null);

  // Membuka detail tugas
  async function viewDetail(id) {
    isDetailModalOpen = true;
    loadingDetail = true;
    detailProofBlob = null;
    try {
      const res = await api.get(`/api/tasks/${id}`);
      detailJob = res.data ?? null;
      if (detailJob?.proof_image_url) {
        try {
          detailProofBlob = await getBlobUrl(detailJob.proof_image_url);
        } catch {
          detailProofBlob = null;
        }
      }
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat detail tugas.'), 'error');
      isDetailModalOpen = false;
    } finally {
      loadingDetail = false;
    }
  }

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

  // Fungsi untuk menyetujui tugas
  async function approveJob(id) {
    try {
      await api.post(`/api/admin/tasks/${id}/approve`, {});
      toast('Tugas disetujui.', 'success');
      await load();
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyetujui tugas.'), 'error');
    }
  }

  // Membuka modal penolakan
  function openRejectModal(id) {
    targetJobId = id;
    rejectionReason = '';
    isRejectModalOpen = true;
  }

  // Eksekusi penolakan dengan alasan
  async function submitRejection(e) {
    e.preventDefault();
    if (!rejectionReason.trim()) {
      toast('Alasan penolakan wajib diisi.', 'error');
      return;
    }

    isSubmitting = true;
    try {
      await api.post(`/api/admin/tasks/${targetJobId}/reject`, {
        reason: rejectionReason
      });
      toast('Tugas berhasil ditolak.', 'success');
      isRejectModalOpen = false;
      await load();
    } catch (err) {
      toast(errorMessage(err, 'Gagal menolak tugas.'), 'error');
    } finally {
      isSubmitting = false;
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
            <th class="p-4">Status & Alasan</th>
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
              <td class="p-4 space-y-1">
                {#if job.status === 'pending'}
                  <span class="inline-block px-2.5 py-1 bg-amber-500/10 border border-amber-500/20 text-amber-400 rounded-lg font-bold text-[10px]">Pending</span>
                {:else if job.status === 'approved'}
                  <span class="inline-block px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg font-bold text-[10px]">Approved</span>
                {:else if job.status === 'rejected'}
                  <span class="inline-block px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg font-bold text-[10px]">Rejected</span>
                  {#if job.rejection_reason || job.reason}
                    <p class="text-[11px] text-rose-400/90 italic mt-1 max-w-xs bg-rose-500/5 p-1.5 rounded border border-rose-500/10">
                      Alasan: "{job.rejection_reason ?? job.reason}"
                    </p>
                  {/if}
                {:else}
                  <span class="inline-block px-2.5 py-1 bg-slate-500/10 border border-slate-500/20 text-slate-300 rounded-lg font-bold text-[10px]">{job.status}</span>
                {/if}
              </td>
              <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button onclick={() => viewDetail(job.id)} class="px-2.5 py-1.5 bg-slate-700/40 hover:bg-slate-700/60 text-slate-200 font-bold rounded-lg transition">Detail</button>
                  {#if job.status === 'pending'}
                    <button onclick={() => approveJob(job.id)} class="px-2.5 py-1.5 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-300 font-bold rounded-lg transition">Setujui</button>
                    <button onclick={() => openRejectModal(job.id)} class="px-2.5 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 font-bold rounded-lg transition">Tolak</button>
                  {/if}
                  <button onclick={() => deleteJob(job.id)} class="p-1.5 hover:bg-rose-500/20 text-rose-400 rounded-lg transition" title="Hapus Tugas">🗑️</button>
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

<!-- Modal Detail Tugas -->
{#if isDetailModalOpen}
  <div class="modal-backdrop" onclick={() => isDetailModalOpen = false}>
    <div class="modal-card detail-modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
        <h3 class="font-bold text-sm text-white">Detail Tugas #{detailJob?.id}</h3>
        <button onclick={() => isDetailModalOpen = false} class="text-slate-400 hover:text-white font-bold">✕</button>
      </div>

      {#if loadingDetail}
        <div class="text-center py-12 text-slate-500 text-xs">Memuat detail tugas...</div>
      {:else if detailJob}
        <div class="space-y-4 text-xs">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="px-2 py-0.5 bg-slate-800 text-slate-300 rounded text-[10px] font-semibold">{detailJob.category?.name ?? '-'}</span>
            <span class="px-2 py-0.5 rounded font-bold text-[10px] {detailJob.status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : detailJob.status === 'approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : detailJob.status === 'rejected' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-slate-500/10 text-slate-300 border border-slate-500/20'}">
              {detailJob.status}
            </span>
          </div>

          <div>
            <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Judul</p>
            <p class="font-bold text-white text-sm">{detailJob.title}</p>
          </div>

          <div>
            <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Deskripsi</p>
            <p class="text-slate-300 leading-relaxed whitespace-pre-line">{detailJob.description || '-'}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Budget</p>
              <p class="font-bold text-emerald-400">{formatRupiah(detailJob.budget)}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Lokasi</p>
              <p class="font-bold text-slate-200">{detailJob.location || '-'}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Deadline</p>
              <p class="font-bold text-slate-200">{detailJob.deadline || '-'}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Pelamar</p>
              <p class="font-bold text-slate-200">{detailJob.applicants_count ?? 0} orang</p>
            </div>
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Pembuat</p>
              <p class="font-bold text-slate-200">{detailJob.owner?.name ?? '-'}</p>
            </div>
            <div>
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500 mb-1">Dibuat</p>
              <p class="font-bold text-slate-200">
                {detailJob.created_at ? new Date(detailJob.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}
              </p>
            </div>
          </div>

          {#if detailJob.status === 'rejected' && detailJob.rejection_reason}
            <div class="p-3 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-300">
              <p class="font-bold mb-1">⚠️ Alasan Penolakan</p>
              <p class="italic leading-relaxed">"{detailJob.rejection_reason}"</p>
            </div>
          {/if}

          {#if detailJob.status === 'reviewing' || (detailJob.proof_url || detailProofBlob)}
            <div class="p-3 rounded-lg bg-slate-800/50 border border-slate-700 space-y-2">
              <p class="text-[10px] uppercase font-bold tracking-wider text-slate-500">📎 Bukti Pekerjaan</p>
              {#if detailJob.proof_url}
                <a href={detailJob.proof_url} target="_blank" rel="noreferrer" class="inline-block px-3 py-2 bg-blue-500/10 text-blue-400 font-bold text-xs rounded-lg transition">
                  🔗 Lihat Bukti (Link)
                </a>
              {/if}
              {#if detailProofBlob}
                <img src={detailProofBlob} alt="Bukti pekerjaan" class="w-full rounded-lg border border-slate-700 max-h-72 object-cover" />
              {/if}
            </div>
          {/if}

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
            {#if detailJob.status === 'pending'}
              <button onclick={() => { isDetailModalOpen = false; approveJob(detailJob.id); }} class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition">
                Setujui
              </button>
              <button onclick={() => { isDetailModalOpen = false; openRejectModal(detailJob.id); }} class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition">
                Tolak
              </button>
            {/if}
            <button onclick={() => isDetailModalOpen = false} class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-lg transition">
              Tutup
            </button>
          </div>
        </div>
      {/if}
    </div>
  </div>
{/if}

<!-- Modal Alasan Penolakan -->
{#if isRejectModalOpen}
  <div class="modal-backdrop" onclick={() => isRejectModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
        <h3 class="font-bold text-sm text-white">Alasan Penolakan Tugas</h3>
        <button onclick={() => isRejectModalOpen = false} class="text-slate-400 hover:text-white font-bold">✕</button>
      </div>

      <form onsubmit={submitRejection} class="space-y-4">
        <div>
          <label for="reason-input" class="block text-xs font-semibold text-slate-300 mb-2">Tuliskan alasan mengapa tugas ini ditolak:</label>
          <textarea 
            id="reason-input"
            bind:value={rejectionReason} 
            rows="3" 
            placeholder="Contoh: Deskripsi kurang jelas atau melanggar ketentuan platform..."
            class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition resize-none"
            required
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button 
            type="button" 
            onclick={() => isRejectModalOpen = false} 
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition"
          >
            Batal
          </button>
          <button 
            type="submit" 
            disabled={isSubmitting}
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition disabled:opacity-50"
          >
            {isSubmitting ? 'Memproses...' : 'Konfirmasi Tolak'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  /* Modal Backdrop Styling */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 100;
  }

  .modal-card {
    background-color: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 20px;
    width: 100%;
    max-width: 440px;
    padding: 20px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  }

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

  :global(body:not(.dark-theme)) .modal-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-card h3 {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .modal-card label {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .modal-card textarea {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card h3 {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card .border-slate-800 {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card .text-white {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card .text-slate-300 {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card .text-slate-200 {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .detail-modal-card .bg-slate-800 {
    background-color: #f1f5f9 !important;
  }
</style>