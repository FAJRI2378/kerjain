<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { initials } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let activeTab = $state('pending');
  let searchQuery = $state('');
  let list = $state([]);
  let loading = $state(true);
  let currentPage = $state(1);
  let lastPage = $state(1);
  let searchTimer = $state(null);

  // State Modal Detail & Review
  let selectedItem = $state(null);
  let isModalOpen = $state(false);
  let rejectionReason = $state('');
  let isSubmitting = $state(false);

  const tabs = [
    { id: 'pending', label: '⏳ Menunggu' },
    { id: 'approved', label: '✓ Disetujui' },
    { id: 'rejected', label: '✕ Ditolak' }
  ];

  async function loadVerifications(page = 1) {
    loading = true;
    try {
      const params = new URLSearchParams({ per_page: '12', page: String(page) });
      if (activeTab) params.set('status', activeTab);
      if (searchQuery.trim()) params.set('search', searchQuery.trim());

      const res = await api.get(`/api/admin/verifications?${params.toString()}`);
      list = res.data ?? [];
      currentPage = res.meta?.current_page ?? page;
      lastPage = res.meta?.last_page ?? 1;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat data verifikasi.'), 'error');
      list = [];
    } finally {
      loading = false;
    }
  }

  function onSearchChange() {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      loadVerifications(1);
    }, 400);
  }

  function switchTab(tabId) {
    activeTab = tabId;
    loadVerifications(1);
  }

  function goToPage(page) {
    if (page < 1 || page > lastPage) return;
    loadVerifications(page);
  }

  onMount(() => loadVerifications(1));

  function openDetailModal(item) {
    selectedItem = item;
    rejectionReason = '';
    isModalOpen = true;
  }

  async function handleApprove(id) {
    isSubmitting = true;
    try {
      await api.post(`/api/admin/verifications/${id}/approve`, {});
      toast('Verifikasi berhasil disetujui! Status pengguna kini Verified.', 'success');
      isModalOpen = false;
      loadVerifications(currentPage);
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyetujui verifikasi.'), 'error');
    } finally {
      isSubmitting = false;
    }
  }

  async function handleReject(id) {
    if (!rejectionReason.trim()) {
      toast('Harap berikan alasan penolakan agar user dapat memperbaiki.', 'error');
      return;
    }

    isSubmitting = true;
    try {
      await api.post(`/api/admin/verifications/${id}/reject`, { reason: rejectionReason });
      toast('Verifikasi ditolak. Catatan terkirim ke pengguna.', 'info');
      isModalOpen = false;
      loadVerifications(currentPage);
    } catch (err) {
      toast(errorMessage(err, 'Gagal menolak verifikasi.'), 'error');
    } finally {
      isSubmitting = false;
    }
  }
</script>

<div class="admin-users-page p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6 users-header">
    <div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold tracking-wider text-purple-400 uppercase">Trust & Safety</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1 users-title">Pusat Verifikasi UMKM & Freelancer</h1>
      <p class="text-xs md:text-sm text-slate-400">Validasi foto tempat usaha fisik UMKM atau rekening & data identitas Freelancer.</p>
    </div>
    <div class="text-xs font-semibold text-purple-300 bg-purple-500/10 border border-purple-500/20 px-4 py-2 rounded-xl pending-badge">
      Total Data: <span class="font-bold text-white badge-count">{list.length}</span>
    </div>
  </div>

  <!-- Tabs + Search -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
    <div class="flex gap-2 overflow-x-auto tab-row">
      {#each tabs as tab}
        <button
          onclick={() => switchTab(tab.id)}
          class={`px-4 py-2 rounded-xl text-xs font-bold transition border whitespace-nowrap tab-btn ${activeTab === tab.id ? 'tab-active' : ''}`}
        >
          {tab.label}
        </button>
      {/each}
    </div>

    <div class="search-wrap">
      <input
        type="text"
        placeholder="Cari nama / email pengaju..."
        bind:value={searchQuery}
        oninput={onSearchChange}
        class="search-input"
      />
    </div>
  </div>

  <!-- Content List -->
  {#if loading}
    <div class="text-center py-20 text-slate-500 text-sm">Memuat data verifikasi...</div>
  {:else if list.length === 0}
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center text-slate-400 space-y-2 empty-card">
      <p class="text-3xl">🗂️</p>
      <p class="font-bold text-white text-base empty-title">Tidak ada data verifikasi</p>
      <p class="text-xs text-slate-500">Belum ada pengajuan dengan status ini.</p>
    </div>
  {:else}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {#each list as item (item.id)}
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl flex flex-col justify-between verify-card">
          <div>
            <!-- Image Preview Thumbnail -->
            {#if item.photo_url}
              <div class="relative h-48 bg-slate-950 overflow-hidden border-b border-slate-800 thumb-box">
                <img src={item.photo_url} alt="Bukti Verifikasi" class="w-full h-full object-cover hover:scale-105 transition duration-500" />
                <div class="absolute top-3 left-3">
                  {#if item.role === 'hirer'}
                    <span class="px-2.5 py-1 bg-blue-500/90 text-white font-bold text-[10px] rounded-lg shadow-md">🏢 UMKM Store</span>
                  {:else}
                    <span class="px-2.5 py-1 bg-emerald-500/95 text-slate-950 font-bold text-[10px] rounded-lg shadow-md">🛠️ Freelancer ID</span>
                  {/if}
                </div>
                <div class="absolute top-3 right-3">
                  {#if item.status === 'approved'}
                    <span class="px-2.5 py-1 bg-emerald-500/90 text-slate-950 font-bold text-[10px] rounded-lg shadow-md">✓ Disetujui</span>
                  {:else if item.status === 'rejected'}
                    <span class="px-2.5 py-1 bg-rose-500/90 text-white font-bold text-[10px] rounded-lg shadow-md">✕ Ditolak</span>
                  {:else}
                    <span class="px-2.5 py-1 bg-amber-500/90 text-slate-950 font-bold text-[10px] rounded-lg shadow-md">⏳ Pending</span>
                  {/if}
                </div>
              </div>
            {/if}

            <!-- Details -->
            <div class="p-5 space-y-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-purple-400 text-xs user-avatar-box">
                  {initials(item.name)}
                </div>
                <div>
                  <h3 class="font-bold text-white text-sm user-name-text">{item.name}</h3>
                  <p class="text-[10px] text-slate-400">{item.email}</p>
                </div>
              </div>

              <!-- Role Specific Container -->
              {#if item.role === 'hirer'}
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 space-y-1 text-xs card-detail-box">
                  <p class="text-slate-300 font-bold detail-bold">{item.business_name}</p>
                  <p class="text-slate-400 text-[11px]">{item.category} • {item.address}</p>
                </div>
              {:else}
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 space-y-1 text-xs card-detail-box">
                  <p class="text-emerald-400 font-bold">💳 {item.bank_name} - {item.account_number}</p>
                  <p class="text-slate-400 text-[11px]">a.n. {item.account_holder_name}</p>
                </div>
              {/if}

              {#if item.note}
                <p class="text-[11px] text-slate-500 italic">Catatan: "{item.note}"</p>
              {/if}
              {#if item.status === 'rejected' && item.admin_note}
                <p class="text-[11px] text-rose-400 italic">Alasan: "{item.admin_note}"</p>
              {/if}
            </div>
          </div>

          <!-- Action Button -->
          <div class="p-5 pt-0">
            <button
              onclick={() => openDetailModal(item)}
              class="w-full py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl transition shadow-lg shadow-purple-600/20"
            >
              🔍 Periksa Data
            </button>
          </div>
        </div>
      {/each}
    </div>

    <!-- Pagination -->
    {#if lastPage > 1}
      <div class="flex items-center justify-center gap-3 pt-2 pagination-row">
        <button
          class="page-nav"
          disabled={currentPage <= 1}
          onclick={() => goToPage(currentPage - 1)}
        >← Sebelumnya</button>
        <span class="text-xs font-semibold text-slate-400">
          Halaman <span class="text-white font-bold">{currentPage}</span> / {lastPage}
        </span>
        <button
          class="page-nav"
          disabled={currentPage >= lastPage}
          onclick={() => goToPage(currentPage + 1)}
        >Berikutnya →</button>
      </div>
    {/if}
  {/if}
</div>

<!-- Modal Periksa & Validasi -->
{#if isModalOpen && selectedItem}
  <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50 modal-backdrop-box">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl p-6 space-y-6 max-h-[90vh] overflow-y-auto modal-content-card">
      <div class="flex justify-between items-start border-b border-slate-800 pb-4 modal-header-box">
        <div>
          <h2 class="text-lg font-black text-white modal-title-text">Detail Pengajuan {selectedItem.role === 'hirer' ? 'UMKM' : 'Freelancer'}</h2>
          <p class="text-xs text-slate-400">
            Status: {selectedItem.status === 'approved' ? '✓ Disetujui' : selectedItem.status === 'rejected' ? '✕ Ditolak' : '⏳ Menunggu Review'}
          </p>
        </div>
        <button onclick={() => isModalOpen = false} class="text-slate-400 hover:text-white text-lg font-bold btn-modal-close">✕</button>
      </div>

      <div class="space-y-4">
        {#if selectedItem.photo_url}
          <div class="rounded-xl overflow-hidden border border-slate-800 bg-slate-950 max-h-[300px] flex items-center justify-center modal-img-preview">
            <img src={selectedItem.photo_url} alt="Full Preview" class="max-h-[300px] w-auto object-contain" />
          </div>
        {/if}

        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2 text-xs info-box-light">
          <p class="font-bold text-slate-300 uppercase tracking-wider modal-info-heading">Informasi Pengaju:</p>
          <p>Nama: <span class="text-white font-semibold modal-info-val">{selectedItem.name}</span></p>
          <p>Email: <span class="text-white font-semibold modal-info-val">{selectedItem.email}</span></p>
          {#if selectedItem.role === 'hirer'}
            <p>Usaha: <span class="text-white font-semibold modal-info-val">{selectedItem.business_name} ({selectedItem.category})</span></p>
            <p>Alamat Toko: <span class="text-white font-semibold modal-info-val">{selectedItem.address}</span></p>
          {:else}
            <p>Rekening Bank/E-Wallet: <span class="text-emerald-400 font-bold">{selectedItem.bank_name} - {selectedItem.account_number}</span></p>
            <p>a.n. <span class="text-emerald-400 font-bold">{selectedItem.account_holder_name}</span></p>
          {/if}
          {#if selectedItem.status === 'rejected' && selectedItem.admin_note}
            <p>Alasan Penolakan: <span class="text-rose-400 font-semibold">{selectedItem.admin_note}</span></p>
          {/if}
        </div>

        {#if selectedItem.status === 'pending'}
          <!-- Panduan Pengecekan -->
          <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2 info-box-light">
            <p class="text-xs font-bold text-slate-300 uppercase tracking-wider modal-info-heading">Panduan Pengecekan Admin:</p>
            <ul class="text-xs text-slate-400 space-y-1.5 list-disc list-inside">
              {#if selectedItem.role === 'hirer'}
                <li>Foto menunjukkan bagian depan toko fisik atau plang usaha UMKM secara jelas.</li>
                <li>Nama dan alamat toko sesuai dengan data pendaftaran profil usaha.</li>
              {:else}
                <li>Nomor rekening dan nama bank valid untuk kelancaran pencairan escrow.</li>
                <li>Identitas akun sesuai dengan data diri pendaftar.</li>
              {/if}
            </ul>
          </div>

          <!-- Form Alasan Penolakan -->
          <div class="space-y-2">
            <label for="reject-reason" class="text-xs font-bold text-slate-300 label-reject">Catatan / Alasan Penolakan <span class="text-slate-500 font-normal">(Wajib diisi jika menolak)</span></label>
            <textarea
              id="reject-reason"
              rows="2"
              bind:value={rejectionReason}
              placeholder="Contoh: Foto toko terlalu gelap / nomor rekening tidak valid. Mohon perbarui."
              class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 focus:outline-none focus:border-purple-500 rejection-textarea"
            ></textarea>
          </div>
        {/if}
      </div>

      {#if selectedItem.status === 'pending'}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800 modal-footer-box">
          <button
            type="button"
            disabled={isSubmitting}
            onclick={() => handleReject(selectedItem.id)}
            class="px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-400 text-xs font-bold rounded-xl transition"
          >
            {isSubmitting ? 'Memproses...' : 'Tolak Verifikasi'}
          </button>

          <button
            type="button"
            disabled={isSubmitting}
            onclick={() => handleApprove(selectedItem.id)}
            class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-emerald-500/10"
          >
            {isSubmitting ? 'Memproses...' : 'Setujui (Verify ✓)'}
          </button>
        </div>
      {/if}
    </div>
  </div>
{/if}

<style>
  /* Light Theme Overrides for Admin Users Verification Page */
  :global(body:not(.dark-theme)) .admin-users-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .users-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .users-title,
  :global(body:not(.dark-theme)) .empty-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .pending-badge {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .badge-count {
    color: #7c3aed !important;
  }

  :global(body:not(.dark-theme)) .verify-card,
  :global(body:not(.dark-theme)) .empty-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  :global(body:not(.dark-theme)) .thumb-box {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .user-avatar-box {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
  }

  :global(body:not(.dark-theme)) .user-name-text {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .card-detail-box {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .detail-bold {
    color: #1e293b !important;
  }

  :global(body:not(.dark-theme)) .search-input {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .tab-btn {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #475569 !important;
  }
  :global(body:not(.dark-theme)) .tab-btn.tab-active {
    background-color: #0d233a !important;
    border-color: #0d233a !important;
    color: #ffffff !important;
  }

  :global(body:not(.dark-theme)) .page-nav {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #334155 !important;
  }

  /* Modal Light Theme Overrides */
  :global(body:not(.dark-theme)) .modal-backdrop-box {
    background-color: rgba(15, 23, 42, 0.5) !important;
  }

  :global(body:not(.dark-theme)) .modal-content-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-header-box,
  :global(body:not(.dark-theme)) .modal-footer-box {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-title-text,
  :global(body:not(.dark-theme)) .modal-info-val,
  :global(body:not(.dark-theme)) .label-reject {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .btn-modal-close {
    color: #64748b !important;
  }
  :global(body:not(.dark-theme)) .btn-modal-close:hover {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .modal-img-preview {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .info-box-light {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-info-heading {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .rejection-textarea {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .rejection-textarea::placeholder {
    color: #94a3b8 !important;
  }

  /* Tabs + Search */
  .tab-row {
    flex-shrink: 0;
  }

  .tab-btn {
    background-color: rgba(51, 65, 85, 0.4);
    border: 1px solid #334155;
    color: #cbd5e1;
  }

  .tab-btn:hover {
    border-color: #7c3aed;
    color: #c4b5fd;
  }

  .tab-btn.tab-active {
    background-color: #7c3aed;
    border-color: #7c3aed;
    color: #ffffff;
  }

  .search-wrap {
    width: 100%;
    max-width: 320px;
    min-width: 0;
  }

  .search-input {
    width: 100%;
    padding: 10px 14px;
    background-color: #0f172a;
    border: 1px solid #334155;
    border-radius: 12px;
    font-size: 12.5px;
    color: #ffffff;
    outline: none;
    transition: border-color 0.2s;
  }

  .search-input::placeholder {
    color: #64748b;
  }

  .search-input:focus {
    border-color: #7c3aed;
  }

  /* Pagination */
  .page-nav {
    padding: 8px 16px;
    background-color: rgba(51, 65, 85, 0.3);
    border: 1px solid #334155;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 0.2s;
  }

  .page-nav:hover:not(:disabled) {
    border-color: #7c3aed;
    color: #c4b5fd;
  }

  .page-nav:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }
</style>