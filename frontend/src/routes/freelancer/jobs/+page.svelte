<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let searchQuery = $state('');
  let selectedCategory = $state('all');
  let maxBudget = $state(5000000); // Filter Budget Tambahan
  let categories = $state([]);
  let jobs = $state([]);
  let loading = $state(true);
  let applyingId = $state(null);

  // Modal Detail
  let isDetailModalOpen = $state(false);
  let detailJob = $state(null);
  let loadingDetail = $state(false);

  async function viewDetail(job) {
    isDetailModalOpen = true;
    loadingDetail = true;
    try {
      const res = await api.get(`/api/tasks/${job.id}`);
      detailJob = res.data ?? null;
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
      const [catRes, jobRes] = await Promise.all([
        api.get('/api/categories'),
        api.get('/api/tasks?per_page=50')
      ]);
      categories = catRes.data ?? [];
      jobs = jobRes.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat lowongan.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  let filteredJobs = $derived(
    jobs.filter(job => {
      const catSlug = job.category?.slug;
      const matchSearch =
        !searchQuery ||
        job.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (job.owner?.name ?? '').toLowerCase().includes(searchQuery.toLowerCase());
      const matchCategory = selectedCategory === 'all' || catSlug === selectedCategory;
      const matchBudget = (job.budget ?? 0) <= maxBudget;
      return matchSearch && matchCategory && matchBudget;
    })
  );

  let appliedIds = $state([]);

  let unverifiedGate = $derived(!!(auth.user && !auth.user.is_verified));

  async function applyJob(jobId, title) {
    applyingId = jobId;
    try {
      await api.post(`/api/tasks/${jobId}/apply`, {});
      appliedIds = [...appliedIds, jobId];
      toast(`Lamaran untuk "${title}" terkirim! Menunggu konfirmasi UMKM.`, 'success');
    } catch (err) {
      if (err.status === 409) {
        appliedIds = [...appliedIds, jobId];
        toast(err.message || 'Kamu sudah melamar tugas ini.', 'info');
      } else {
        toast(errorMessage(err, 'Gagal mengirim lamaran.'), 'error');
      }
    } finally {
      applyingId = null;
    }
  }
</script>

<div class="jobs-page">
  <!-- Top Banner / Header dengan Filter Budget Lanjutan -->
  <div class="page-header">
    <div class="header-info">
      <h1 class="page-title">Eksplor Lowongan Tugas</h1>
      <p class="page-sub">Temukan tugas harian atau sampingan dari UMKM sekitar dan mulai hasilkan uang.</p>
    </div>

    <div class="header-controls">
      <div class="search-box">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input
          type="text"
          placeholder="Cari tugas atau nama usaha..."
          bind:value={searchQuery}
        />
      </div>
    </div>
  </div>

  {#if unverifiedGate}
    <div class="verify-gate">
      <div class="gate-icon">🪪</div>
      <h2 class="gate-title">Verifikasi Identitas Dulu, Yuk!</h2>
      <p class="gate-sub">Untuk melamar pekerjaan, kamu perlu verifikasi identitas. Prosesnya cepat — maksimal 1×24 jam.</p>
      <a href="/freelancer/id" class="btn-gate">Ajukan Verifikasi Sekarang</a>
    </div>
  {:else if loading}
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Memuat lowongan...</p>
    </div>
  {:else}
    <!-- Category Filter Tabs -->
    {#if categories.length > 0}
      <div class="category-filters">
        <button 
          class="cat-tab {selectedCategory === 'all' ? 'active' : ''}" 
          onclick={() => selectedCategory = 'all'}>
          Semua Kategori
        </button>
        {#each categories as cat}
          <button 
            class="cat-tab {selectedCategory === cat.slug ? 'active' : ''}" 
            onclick={() => selectedCategory = cat.slug}>
            {cat.name}
          </button>
        {/each}
      </div>
    {/if}

    <!-- Jobs Grid -->
    <div class="jobs-grid">
      {#each filteredJobs as job (job.id)}
        <div class="job-card">
          <div class="card-head">
            <span class="badge-cat">
              {job.category?.name ?? 'Tugas Mikro'}
            </span>
            <span class="meta-location">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
              {job.location ?? 'Lokasi Lokal'}
            </span>
          </div>

          <div class="card-body">
            <h3 class="job-title">{job.title}</h3>
            <p class="job-employer">
              Pemberi Kerja: <strong>{job.owner?.name ?? '-'}</strong>
              {#if job.deadline}
                <span class="meta-dot">•</span>
                <span class="deadline-text">⏱️ {job.deadline}</span>
              {/if}
            </p>
            {#if job.description}
              <p class="job-desc-preview">{job.description}</p>
            {/if}
          </div>

          <div class="card-foot">
            <div class="budget-col">
              <span class="budget-label">Honor Pekerjaan</span>
              <span class="budget-value">{formatRupiah(job.budget)}</span>
            </div>

            <button
              onclick={() => applyJob(job.id, job.title)}
              disabled={appliedIds.includes(job.id) || applyingId === job.id}
              class="btn-apply {appliedIds.includes(job.id) ? 'applied' : ''}">
              {appliedIds.includes(job.id) ? '✓ Sudah Dilamar' : applyingId === job.id ? 'Mengirim...' : 'Lamar Tugas'}
            </button>

            <button
              onclick={() => viewDetail(job)}
              class="btn-detail">
              Lihat Detail
            </button>
          </div>
        </div>
      {:else}
        <div class="empty-state">
          <p>Tidak ada pekerjaan yang sesuai dengan kriteria pencarian atau batas budget Anda.</p>
        </div>
      {/each}
    </div>
  {/if}
</div>

<!-- Modal Detail Tugas -->
{#if isDetailModalOpen}
  <div class="modal-backdrop" onclick={() => isDetailModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-head">
        <h3 class="modal-title">Detail Tugas</h3>
        <button onclick={() => isDetailModalOpen = false} class="modal-close">✕</button>
      </div>

      {#if loadingDetail}
        <div class="modal-loading">Memuat detail tugas...</div>
      {:else if detailJob}
        <div class="modal-body">
          <div class="modal-tags">
            <span class="badge-cat">{detailJob.category?.name ?? 'Tugas Mikro'}</span>
            <span class="modal-location">📍 {detailJob.location ?? 'Lokasi Lokal'}</span>
          </div>

          <h4 class="modal-job-title">{detailJob.title}</h4>
          <p class="modal-employer">
            Pemberi Kerja: <strong>{detailJob.owner?.name ?? '-'}</strong>
          </p>

          <div class="modal-desc">
            <p>{detailJob.description || 'Tidak ada deskripsi.'}</p>
          </div>

          <div class="modal-budget-row">
            <div>
              <span class="detail-label">Honor Pekerjaan</span>
              <span class="budget-value">{formatRupiah(detailJob.budget)}</span>
            </div>
            {#if detailJob.deadline}
              <div>
                <span class="detail-label">Deadline</span>
                <span class="deadline-text">⏱️ {detailJob.deadline}</span>
              </div>
            {/if}
          </div>

          <div class="modal-foot">
            <button
              onclick={() => applyJob(detailJob.id, detailJob.title)}
              disabled={appliedIds.includes(detailJob.id) || applyingId === detailJob.id}
              class="btn-apply {appliedIds.includes(detailJob.id) ? 'applied' : ''}">
              {appliedIds.includes(detailJob.id) ? '✓ Sudah Dilamar' : applyingId === detailJob.id ? 'Mengirim...' : 'Lamar Tugas Ini'}
            </button>
            <button onclick={() => isDetailModalOpen = false} class="btn-close-modal">Tutup</button>
          </div>
        </div>
      {/if}
    </div>
  </div>
{/if}

<style>
  .jobs-page {
    max-width: 1240px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  /* Page Header */
  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  @media (min-width: 768px) {
    .page-header {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  .page-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
  }

  .header-controls {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 360px;
  }

  .search-box {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 8px 14px;
    display: flex;
    align-items: center;
    width: 100%;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .search-box:focus-within {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .search-icon {
    margin-right: 10px;
    flex-shrink: 0;
  }

  .search-box input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 13.5px;
    color: #0f172a;
    background: transparent;
  }

  .budget-filter-box {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 4px 2px;
  }

  .budget-filter-label {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
  }

  .range-slider {
    width: 100%;
    accent-color: #15803d;
  }

  /* Category Filters */
  .category-filters {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .cat-tab {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
  }

  .cat-tab.active {
    background: #0d233a;
    color: #ffffff;
    border-color: #0d233a;
  }

  /* Loading State */
  .loading-state {
    text-align: center;
    padding: 60px 0;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
  }

  /* Verify Gate */
  .verify-gate {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 56px 32px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  .gate-icon {
    font-size: 44px;
    margin-bottom: 4px;
  }

  .gate-title {
    font-size: 20px;
    font-weight: 800;
    color: #0d233a;
    margin: 0;
  }

  .gate-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0 auto;
    max-width: 460px;
    line-height: 1.6;
  }

  .btn-gate {
    display: inline-block;
    margin-top: 12px;
    background: #15803d;
    color: #ffffff;
    text-decoration: none;
    padding: 12px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);
  }

  .btn-gate:hover {
    background: #166534;
  }

  :global(body.dark-theme .verify-gate) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .gate-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .gate-sub) {
    color: #94a3b8 !important;
  }

  /* Jobs Grid */
  .jobs-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  @media (min-width: 768px) {
    .jobs-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .job-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s, background-color 0.3s ease;
  }

  .job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
    border-color: #cbd5e1;
  }

  .card-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .badge-cat {
    background-color: #dcfce7;
    color: #166534;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
  }

  .meta-location {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
  }

  .job-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 6px;
    line-height: 1.35;
  }

  .job-employer {
    font-size: 12.5px;
    color: #64748b;
    margin: 0 0 8px;
  }

  .job-employer strong {
    color: #334155;
  }

  .job-desc-preview {
    font-size: 12.5px;
    color: #475569;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .meta-dot {
    margin: 0 4px;
    color: #cbd5e1;
  }

  .deadline-text {
    color: #b45309;
    font-weight: 600;
  }

  .card-foot {
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
  }

  .btn-detail {
    background-color: #ffffff;
    color: #15803d;
    border: 1px solid #15803d;
    padding: 9px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
    white-space: nowrap;
  }

  .btn-detail:hover {
    background-color: #f0fdf4;
  }

  :global(body.dark-theme .btn-detail) {
    background-color: #15803d;
    color: #ffffff;
    border-color: #15803d;
  }

  :global(body.dark-theme .btn-detail:hover) {
    background-color: #166534;
  }

  .budget-label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 2px;
  }

  .budget-value {
    font-size: 18px;
    font-weight: 800;
    color: #15803d;
  }

  .btn-apply {
    background-color: #15803d;
    color: #ffffff;
    border: none;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
  }

  .btn-apply:hover:not(:disabled) {
    background-color: #166534;
  }

  .btn-apply.applied {
    background-color: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
    box-shadow: none;
  }

  .btn-apply:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }

  .empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 48px 24px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    color: #64748b;
    font-size: 13.5px;
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .job-card),
  :global(body.dark-theme .empty-state) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .job-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .job-employer),
  :global(body.dark-theme .job-desc-preview),
  :global(body.dark-theme .budget-filter-label) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .search-box) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .search-box input) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .cat-tab) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .cat-tab.active) {
    background-color: #15803d !important;
    border-color: #15803d !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .card-foot) {
    border-color: #334155 !important;
  }

  /* Modal Detail */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 100;
  }

  .modal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    width: 100%;
    max-width: 460px;
    padding: 24px;
    box-shadow: 0 20px 30px -8px rgba(0, 0, 0, 0.25);
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 12px;
    margin-bottom: 16px;
  }

  .modal-title {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .modal-close {
    background: #f1f5f9;
    border: none;
    color: #64748b;
    font-weight: 700;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    cursor: pointer;
  }

  .modal-loading {
    text-align: center;
    padding: 40px 0;
    color: #94a3b8;
    font-size: 13px;
  }

  .modal-body {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .modal-tags {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .modal-location {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
  }

  .modal-job-title {
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    line-height: 1.35;
  }

  .modal-employer {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
  }

  .modal-employer strong {
    color: #334155;
  }

  .modal-desc {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
  }

  .modal-desc p {
    font-size: 12.5px;
    color: #475569;
    margin: 0;
    line-height: 1.6;
    white-space: pre-line;
  }

  .modal-budget-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 8px;
  }

  .detail-label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 2px;
  }

  .modal-foot {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
  }

  .btn-close-modal {
    background: #f1f5f9;
    border: none;
    color: #475569;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
  }

  :global(body.dark-theme .modal-card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .modal-head) {
    border-color: #334155 !important;
  }
  :global(body.dark-theme .modal-title),
  :global(body.dark-theme .modal-job-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .modal-close) {
    background: #0f172a !important;
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .modal-employer),
  :global(body.dark-theme .modal-location) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .modal-employer strong) {
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .modal-desc) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .modal-desc p) {
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .modal-foot) {
    border-color: #334155 !important;
  }
  :global(body.dark-theme .btn-close-modal) {
    background: #0f172a !important;
    color: #cbd5e1 !important;
  }
</style>