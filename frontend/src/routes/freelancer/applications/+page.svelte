<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { waLink } from '$lib/whatsapp.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let loading = $state(true);
  let activeFilter = $state("Semua");
  let applications = $state([]);

  // Modal Detail Tugas
  let isDetailModalOpen = $state(false);
  let detailJob = $state(null);
  let loadingDetail = $state(false);
  let selectedAppStatus = $state(null);

  async function loadApplications() {
    loading = true;
    try {
      const res = await api.get('/api/freelancer/applications');
      applications = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat data lamaran.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(loadApplications);

  async function viewDetail(app) {
    isDetailModalOpen = true;
    loadingDetail = true;
    detailJob = null;
    selectedAppStatus = null;
    try {
      const res = await api.get(`/api/tasks/${app.job.id}`);
      detailJob = res.data ?? null;
      selectedAppStatus = app.status;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat detail tugas.'), 'error');
      isDetailModalOpen = false;
    } finally {
      loadingDetail = false;
    }
  }

  // Filter reaktif berdasarkan tab status
  let filteredApplications = $derived(
    activeFilter === "Semua" 
      ? applications 
      : applications.filter(app => app.status === activeFilter)
  );

  // Fungsi batalkan lamaran (terhubung ke API)
  async function cancelApplication(appId) {
    try {
      await api.post(`/api/freelancer/applications/${appId}/cancel`, {});
      applications = applications.filter(app => app.id !== appId);
      toast('Lamaran berhasil dibatalkan.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal membatalkan lamaran.'), 'error');
    }
  }

  // Status yang tampil: bila lamaran sudah diterima, ikuti status JOB yang sebenarnya
  // (in_progress / reviewing / completed), bukan status lamaran yang statis.
  function jobProgress(app) {
    const js = app.job?.status;
    if (app.status === 'accepted' && ['in_progress', 'reviewing', 'completed'].includes(js)) {
      return js;
    }
    return app.status;
  }

  function statusView(app) {
    const map = {
      accepted: { label: '🎉 DITERIMA UMKM', cls: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20' },
      rejected: { label: '❌ TIDAK DITERIMA', cls: 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' },
      pending: { label: '⏳ MENINJAU (PENDING)', cls: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20' },
      cancelled: { label: '🚫 DIBATALKAN', cls: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20' },
      in_progress: { label: '🚧 SEDANG DIKERJAKAN', cls: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20' },
      reviewing: { label: '🔍 SEDANG DIREVIEW', cls: 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20' },
      completed: { label: '✅ SELESAI', cls: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20' }
    };
    return map[jobProgress(app)] ?? map.pending;
  }

  function noteText(app) {
    const s = jobProgress(app);
    if (s === 'in_progress') {
      return app.job?.revision_note
        ? `UMKM meminta revisi: "${app.job.revision_note}". Perbaiki lalu kirim ulang bukti pekerjaanmu.`
        : 'Tugas sedang kamu kerjakan. Selesaikan lalu kirim bukti pekerjaanmu.';
    }
    if (s === 'reviewing') return 'Bukti pekerjaan sudah dikirim dan sedang direview oleh UMKM.';
    if (s === 'completed') return 'Tugas telah selesai dan dana sudah dibayarkan ke dompetmu.';
    return app.message;
  }
</script>

<svelte:head>
  <title>Status Lamaran - Freelancer Portal</title>
</svelte:head>

<div class="p-6 md:p-10 space-y-6 font-sans applications-page max-w-5xl mx-auto">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 header-box">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Portal Freelancer</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight page-title">Status Lamaran Saya</h1>
      <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 page-sub">Pantau proses seleksi tugas mikromu dari berbagai UMKM lokal.</p>
    </div>
    
    <a href="/freelancer/jobs" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 flex-shrink-0">
      <span>🔍</span> Cari Tugas Lain
    </a>
  </div>

  <div class="flex gap-2 overflow-x-auto pb-1">
    {#each [
      { label: 'Semua', val: 'Semua' },
      { label: '🎉 Diterima', val: 'accepted' },
      { label: '⏳ Menunggu', val: 'pending' },
      { label: '❌ Ditolak', val: 'rejected' }
    ] as filter}
      <button 
        onclick={() => activeFilter = filter.val}
        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap border
          {activeFilter === filter.val 
            ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900 dark:border-white shadow-sm' 
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800 dark:hover:bg-slate-800 filter-btn'}"
      >
        {filter.label}
      </button>
    {/each}
  </div>

  <div class="space-y-4">
    {#if loading}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 text-xs shadow-sm">
        Memuat data lamaran...
      </div>
    {:else if filteredApplications.length === 0}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3 empty-box shadow-sm">
        <p class="text-3xl">📭</p>
        <p class="font-bold text-slate-900 dark:text-white text-base text-dark-fix">Tidak ada riwayat lamaran</p>
        <p class="text-xs text-slate-500 dark:text-slate-400">Kamu belum memiliki riwayat lamaran dengan kategori status ini.</p>
      </div>
    {:else}
      {#each filteredApplications as app (app.id)}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-5 app-item-card">
          
          <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2.5 py-0.5 font-extrabold text-[11px] rounded-md border {statusView(app).cls}">
                {statusView(app).label}
              </span>

              <span class="text-[11px] font-mono text-slate-400">• Dilamar: {new Date(app.applied_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
            </div>

            <h3 class="font-black text-base text-slate-900 dark:text-white app-title text-dark-fix leading-snug">{app.job.title}</h3>
            
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
              <span>🏢 {app.job.employer}</span>
              <span>•</span>
              <span class="text-emerald-600 dark:text-emerald-400 font-bold">{formatRupiah(app.job.reward)}</span>
            </div>

            <div class="text-[11px] p-3 rounded-xl feedback-box font-medium leading-relaxed">
              <strong>Catatan:</strong> "{noteText(app)}"
            </div>
          </div>

          <div class="w-full md:w-auto flex items-center justify-end gap-2 pt-3 md:pt-0 border-t md:border-0 border-slate-100 dark:border-slate-800">
            <button 
              onclick={() => viewDetail(app)}
              class="w-full md:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition"
            >
              Detail Tugas
            </button>
            {#if jobProgress(app) === 'in_progress'}
              <a href="/freelancer/mytasks" class="w-full md:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center">
                Lanjut Kerjakan 🚀
              </a>
            {:else if jobProgress(app) === 'reviewing'}
              <a href="/freelancer/mytasks" class="w-full md:w-auto px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center">
                Cek Status Review ⇀
              </a>
            {:else if jobProgress(app) === 'completed'}
              <a href="/freelancer/mytasks" class="w-full md:w-auto px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center">
                Lihat Riwayat ↔
              </a>
            {:else if app.status === 'accepted'}
              <a href="/freelancer/mytasks" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center">
                Mulai Kerjakan 🚀
              </a>
            {:else if app.status === 'rejected' || app.status === 'cancelled'}
              <button class="w-full md:w-auto px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed" disabled>
                Selesai / Ditutup
              </button>
            {:else}
              <button 
                onclick={() => cancelApplication(app.id)}
                class="w-full md:w-auto px-4 py-2.5 bg-slate-100 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-950/30 text-slate-700 hover:text-red-600 dark:text-slate-300 dark:hover:text-red-400 text-xs font-bold rounded-xl transition border border-slate-200 dark:border-slate-700"
              >
                Batalkan Lamaran
              </button>
            {/if}
          </div>

        </div>
      {/each}
    {/if}
  </div>
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
            <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
              {detailJob.category?.name ?? 'Tugas Mikro'}
            </span>
            <span class="modal-location">📍 {detailJob.location ?? 'Lokasi Lokal'}</span>
          </div>

          <h4 class="modal-job-title">{detailJob.title}</h4>
          <p class="modal-employer">Pemberi Kerja: <strong>{detailJob.owner?.name ?? '-'}</strong></p>

          {#if selectedAppStatus === 'accepted' && waLink(detailJob.owner?.phone)}
            <a 
              href={waLink(detailJob.owner?.phone, `Halo ${detailJob.owner?.name}, saya ${'freelancer'} dari Kerjain, mengerjakan tugas \"${detailJob.title}\" (#${detailJob.id}).`)}
              target="_blank" rel="noreferrer"
              class="block w-full text-center px-4 py-2.5 bg-[#25d366] hover:bg-[#1eb858] text-white font-bold rounded-xl transition text-xs"
            >
              💬 Hubungi UMKM via WhatsApp
            </a>
          {/if}

          <div class="modal-desc">
            <p>{detailJob.description || 'Tidak ada deskripsi.'}</p>
          </div>

          <div class="modal-budget-row">
            <div>
              <span class="detail-label">Honor Pekerjaan</span>
              <span class="budget-value">{formatRupiah(detailJob.budget)}</span>
            </div>
            {#if detailJob.status === 'in_progress' && detailJob.worker?.name}
              <div>
                <span class="detail-label">Worker Aktif</span>
                <span class="modal-location">{detailJob.worker.name}</span>
              </div>
            {/if}
          </div>

          <div class="modal-foot">
            {#if detailJob.status === 'pending'}
              <span class="modal-status">⏳ Menunggu Review Admin</span>
            {:else}
              <span class="modal-status">Status: {detailJob.status}</span>
            {/if}
            <button onclick={() => isDetailModalOpen = false} class="btn-close-modal">Tutup</button>
          </div>
        </div>
      {/if}
    </div>
  </div>
{/if}

<style>
  /* ---------------- LIGHT MODE CUSTOM OVERRIDES ---------------- */
  :global(body:not(.dark-theme)) .applications-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-title,
  :global(body:not(.dark-theme)) .app-title,
  :global(body:not(.dark-theme)) .text-dark-fix {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-sub {
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .header-box {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .app-item-card,
  :global(body:not(.dark-theme)) .empty-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  }

  :global(body:not(.dark-theme)) .feedback-box {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #e2e8f0;
  }

  /* ---------------- DARK MODE CUSTOM OVERRIDES ---------------- */
  :global(body.dark-theme) .feedback-box {
    background-color: #0f172a !important;
    color: #94a3b8 !important;
    border: 1px solid #334155;
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
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
  }

  .modal-status {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
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
  :global(body.dark-theme .modal-location),
  :global(body.dark-theme .modal-status) {
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