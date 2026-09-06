<script>
  import { onMount } from 'svelte';
  import { api, getBlobUrl } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { waLink } from '$lib/whatsapp.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let myTasks = $state([]);
  let loading = $state(true);
  let proofOpen = $state({});
  let proofInput = $state({});
  let proofFile = $state({});
  let proofPreview = $state({});
  let proofBlobs = $state({});
  let submittingId = $state(null);

  // Detail modal riwayat (mengikuti halaman Status Lamaran)
  let detailTask = $state(null);
  let loadingDetail = $state(false);

  // Riwayat = pekerjaan yang sudah selesai / dibatalkan, ditampilkan di bawah kanban
  let historyTasks = $derived(
    myTasks.filter((t) => t.status === 'completed' || t.status === 'cancelled')
  );

  // Definisi Kolom Kanban (progres aktif saja; selesai masuk ke Riwayat)
  const columns = [
    { id: 'in_progress', title: '📋 Dalam Pengerjaan', color: 'border-slate-200' },
    { id: 'reviewing', title: '⏳ Sedang Direview', color: 'border-blue-200' }
  ];

  async function openDetail(taskId) {
    loadingDetail = true;
    try {
      const res = await api.get(`/api/tasks/${taskId}`);
      detailTask = res.data ?? res;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat detail tugas.'), 'error');
    } finally {
      loadingDetail = false;
    }
  }

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/tasks/mine');
      myTasks = res.data ?? [];
      for (const t of myTasks) {
        if (t.proof_image_url) await ensureProofBlob(t.id, t.proof_image_url);
      }
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat tugas.'), 'error');
    } finally {
      loading = false;
    }
  }

  async function ensureProofBlob(taskId, taskUrl) {
    if (!taskUrl || proofBlobs[taskId]) return;
    try {
      proofBlobs[taskId] = await getBlobUrl(taskUrl);
    } catch {
      proofBlobs[taskId] = null;
    }
  }

  function onPickFile(taskId, e) {
    const file = e.target.files?.[0];
    if (!file) return;
    if (proofPreview[taskId]) URL.revokeObjectURL(proofPreview[taskId]);
    proofFile[taskId] = file;
    proofPreview[taskId] = URL.createObjectURL(file);
    proofInput[taskId] = '';
  }

  function clearProof(taskId) {
    proofInput[taskId] = '';
    proofFile[taskId] = null;
    if (proofPreview[taskId]) {
      URL.revokeObjectURL(proofPreview[taskId]);
      proofPreview[taskId] = null;
    }
  }

  onMount(load);

  async function submitProof(taskId) {
    const url = (proofInput[taskId] ?? '').trim();
    const file = proofFile[taskId];

    if (!file && !url) {
      toast('Masukkan link bukti ATAU foto bukti pekerjaan.', 'error');
      return;
    }
    submittingId = taskId;
    try {
      let res;
      if (file) {
        const fd = new FormData();
        fd.append('proof', file);
        res = await api.post(`/api/tasks/${taskId}/submit`, fd);
      } else {
        res = await api.post(`/api/tasks/${taskId}/submit`, { proof_url: url });
      }

      const updated = res.data ?? res;
      myTasks = myTasks.map((t) => (t.id === taskId ? updated : t));
      if (updated.proof_image_url) await ensureProofBlob(taskId, updated.proof_image_url);

      clearProof(taskId);
      proofOpen[taskId] = false;
      toast('Bukti kerja terkirim! Tugas pindah ke tahap Review.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengirim bukti.'), 'error');
    } finally {
      submittingId = null;
    }
  }

  async function downloadPdf(task) {
    const invoiceId = task.invoice?.id;
    if (!invoiceId) {
      toast('Invoice belum tersedia untuk tugas ini.', 'error');
      return;
    }
    try {
      const url = await getBlobUrl(`/api/invoices/${invoiceId}/pdf`);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${task.invoice?.number ?? `INV-${task.id}`}.pdf`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 1500);
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengunduh invoice.'), 'error');
    }
  }
</script>

<div class="tasks-page font-sans">
  <!-- Header (Disembunyikan saat print) -->
  <div class="page-header no-print">
    <h1 class="page-title">Tugas Saya </h1>
    <p class="page-sub">Pantau alur kerja Anda. Kartu akan otomatis berpindah kolom setelah bukti dikirim.</p>
  </div>

  {#if loading}
    <div class="loading-state no-print">
      <div class="spinner"></div>
      <p>Memuat tugas...</p>
    </div>
  {:else if myTasks.length === 0}
    <div class="empty-state no-print">
      <p>Belum ada tugas yang kamu kerjakan. Cek daftar lowongan untuk mulai bekerja!</p>
    </div>
  {:else}
    <!-- Kanban Board Area (Disembunyikan saat print) -->
    <div class="kanban-board no-print">
      {#each columns as col}
        <div class="kanban-column {col.color}">
          <h3 class="column-title">{col.title}</h3>
          
          <div class="kanban-cards">
            {#each myTasks.filter(t => t.status === col.id) as task (task.id)}
              <div class="task-card">
                <div class="task-head font-mono">
                  <span class="task-id">#{task.id}</span>
                </div>

                <h4 class="task-title">{task.title}</h4>
                <p class="task-owner">🏢 {task.owner?.name ?? '-'}</p>
                
                {#if task.proof_url}
                  <a href={task.proof_url} target="_blank" rel="noreferrer" class="proof-link">
                    📎 Lihat Bukti Terkirim
                  </a>
                {/if}
                {#if proofBlobs[task.id]}
                  <img src={proofBlobs[task.id]} alt="Bukti pekerjaan" class="proof-img" />
                {/if}

                {#if task.status === 'in_progress' && task.revision_note}
                  <div class="revision-note">
                    <strong>⚠️ Catatan Revisi:</strong> {task.revision_note}
                  </div>
                {/if}

                {#if waLink(task.owner?.phone)}
                  <a 
                    href={waLink(task.owner?.phone, `Halo ${task.owner?.name}, saya mengerjakan tugas \"${task.title}\" (#${task.id}).`)}
                    target="_blank" rel="noreferrer"
                    class="btn-wa"
                  >
                    💬 Hubungi UMKM
                  </a>
                {/if}

                <div class="task-footer">
                  <span class="task-price">{formatRupiah(task.budget || task.price || 0)}</span>
                  
                  <!-- Tombol Aksi Berdasarkan Kolom -->
                  {#if task.status === 'in_progress'}
                    <div class="proof-action-group">
                      {#if proofOpen[task.id]}
                        <div class="proof-input-box">
                          <input
                            bind:value={proofInput[task.id]}
                            placeholder="Link (Drive/Imgur) - opsional"
                          />
                          <label class="file-pick">
                            <input
                              type="file"
                              accept="image/jpeg,image/jpg,image/png,image/webp"
                              onchange={(e) => onPickFile(task.id, e)}
                              class="file-input"
                            />
                            {proofFile[task.id] ? `📷 ${proofFile[task.id].name}` : '📷 Pilih Foto Bukti (opsional)'}
                          </label>
                          {#if proofPreview[task.id]}
                            <img src={proofPreview[task.id]} alt="Pratinjau bukti" class="proof-preview" />
                          {/if}
                          <button 
                            onclick={() => submitProof(task.id)} 
                            disabled={submittingId === task.id}
                            class="btn-send">
                            {submittingId === task.id ? '...' : 'Kirim'}
                          </button>
                        </div>
                      {:else}
                        <button onclick={() => proofOpen[task.id] = true} class="btn-upload">
                          Upload Bukti
                        </button>
                      {/if}
                    </div>
                  {:else if task.status === 'reviewing'}
                    <span class="badge-waiting">⏳ Menunggu</span>
                  {:else}
                    <button class="btn-print" onclick={() => downloadPdf(task)}>
                      📥 Unduh Invoice PDF
                    </button>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        </div>
      {/each}
    </div>

    <!-- Riwayat Pekerjaan (mengikuti halaman Status Lamaran) -->
    {#if historyTasks.length > 0}
      <div class="history-section no-print">
        <div class="history-header">
          <h2 class="history-title">📜 Riwayat Pekerjaan</h2>
          <p class="history-sub">Pekerjaan yang sudah selesai atau dibatalkan, lengkap dengan riwayatnya.</p>
        </div>

        <div class="history-list">
          {#each historyTasks as task (task.id)}
            <div class="history-card">
              <div class="flex flex-wrap items-center gap-2">
                {#if task.status === 'cancelled'}
                  <span class="px-2.5 py-0.5 bg-slate-500/10 text-slate-600 dark:text-slate-400 font-extrabold text-[11px] rounded-md border border-slate-500/20">
                    🚫 DIBATALKAN
                  </span>
                {:else}
                  <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
                    ✅ SELESAI
                  </span>
                {/if}

                <span class="text-[11px] font-mono text-slate-400">
                  • {task.updated_at ? new Date(task.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}
                </span>
              </div>

              <h4 class="history-task-title">{task.title}</h4>

              <div class="history-meta">
                <span>🏢 {task.owner?.name ?? '-'}</span>
                <span>•</span>
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">{formatRupiah(task.budget || task.price || 0)}</span>
              </div>

              <div class="history-actions">
                <button onclick={() => openDetail(task.id)} class="btn-detail">
                  Detail Tugas
                </button>
                {#if task.status === 'completed'}
                  <button onclick={() => downloadPdf(task)} class="btn-invoice">
                    📥 Unduh Invoice PDF
                  </button>
                {/if}
                {#if waLink(task.owner?.phone)}
                  <a 
                    href={waLink(task.owner?.phone, `Halo ${task.owner?.name}, saya dari Kerjain, saya telah mengerjakan tugas \"${task.title}\" (#${task.id}).`)}
                    target="_blank" rel="noreferrer"
                    class="btn-wa-history"
                  >
                    💬 Hubungi UMKM
                  </a>
                {/if}
              </div>
            </div>
          {/each}
        </div>
      </div>
    {/if}
  {/if}

  <!-- Modal Detail Tugas (Riwayat) -->
  {#if detailTask}
    <div class="modal-backdrop" onclick={() => detailTask = null}>
      <div class="modal-card" onclick={(e) => e.stopPropagation()}>
        <div class="modal-head">
          <h3 class="modal-title">Detail Tugas</h3>
          <button onclick={() => detailTask = null} class="modal-close">✕</button>
        </div>

        {#if loadingDetail}
          <div class="modal-loading">Memuat detail tugas...</div>
        {:else}
          <div class="modal-body">
            <div class="modal-tags">
              <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
                {detailTask.category?.name ?? 'Tugas Mikro'}
              </span>
              <span class="modal-location">📍 {detailTask.location ?? 'Lokasi Lokal'}</span>
            </div>

            <h4 class="modal-job-title">{detailTask.title}</h4>
            <p class="modal-employer">Pemberi Kerja: <strong>{detailTask.owner?.name ?? '-'}</strong></p>

            <div class="modal-desc">
              <p>{detailTask.description || 'Tidak ada deskripsi.'}</p>
            </div>

            <div class="modal-budget-row">
              <div>
                <span class="detail-label">Honor Pekerjaan</span>
                <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">{formatRupiah(detailTask.budget)}</span>
              </div>
              <div class="text-right">
                <span class="detail-label">Status</span>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                  {detailTask.status === 'completed' ? '✅ Selesai' : detailTask.status === 'cancelled' ? '🚫 Dibatalkan' : detailTask.status}
                </span>
              </div>
            </div>

            {#if detailTask.proof_url}
              <a href={detailTask.proof_url} target="_blank" rel="noreferrer" class="proof-link">
                📎 Lihat Bukti Terkirim
              </a>
            {/if}
            {#if proofBlobs[detailTask.id]}
              <img src={proofBlobs[detailTask.id]} alt="Bukti pekerjaan" class="proof-img" />
            {/if}

            {#if detailTask.status === 'completed'}
              <button onclick={() => downloadPdf(detailTask)} class="btn-invoice w-full text-center">
                📥 Unduh Invoice PDF
              </button>
            {/if}
          </div>
        {/if}
      </div>
    </div>
  {/if}
</div>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
  }

  * { box-sizing: border-box; }

  .tasks-page {
    padding: 32px 24px;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
  }

  .page-sub {
    color: #64748b;
    font-size: 14px;
    margin: 0;
  }

  .loading-state, .empty-state {
    text-align: center;
    padding: 48px;
    color: #64748b;
    background: #ffffff;
    border-radius: 16px;
    border: 1px dashed #cbd5e1;
  }

  /* KANBAN BOARD STYLES */
  .kanban-board {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 16px;
    min-height: 60vh;
  }

  .kanban-column {
    flex: 1;
    min-width: 320px;
    background: #f8fafc;
    border-top: 4px solid;
    border-radius: 12px;
    padding: 16px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
  }

  .column-title {
    font-size: 15px;
    font-weight: 800;
    color: #334155;
    margin: 0 0 16px;
  }

  .kanban-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .task-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .task-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
  }

  .task-head {
    margin-bottom: 8px;
  }
  .task-id {
    font-size: 12px;
    font-weight: 700;
    color: #15803d;
    background: #dcfce7;
    padding: 2px 6px;
    border-radius: 4px;
  }

  .task-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px;
    line-height: 1.4;
  }

  .task-owner {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 8px;
  }

  .proof-link {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    padding: 4px 8px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 12px;
  }

  .proof-img {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 12px;
  }

  .proof-preview {
    width: 100%;
    max-height: 160px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
  }

  .revision-note {
    font-size: 11px;
    font-weight: 600;
    color: #b45309;
    background: #fef3c7;
    border: 1px solid #fde68a;
    padding: 8px 10px;
    border-radius: 8px;
    margin-bottom: 12px;
    line-height: 1.4;
  }

  .btn-wa {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    background: #25d366;
    padding: 5px 10px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 12px;
  }

  .btn-wa:hover {
    background: #1eb858;
  }

  .file-pick {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #4b5563;
    background: #f1f5f9;
    border: 1px dashed #cbd5e1;
    border-radius: 6px;
    padding: 7px 10px;
    cursor: pointer;
  }

  .file-pick:hover {
    border-color: #15803d;
    color: #15803d;
  }

  .file-input {
    display: none;
  }

  .task-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #e2e8f0;
    padding-top: 14px;
    margin-top: 6px;
  }

  .task-price {
    font-size: 14px;
    font-weight: 800;
    color: #15803d;
  }

  /* Inputs & Buttons */
  .proof-input-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
  }

  .proof-input-box input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 11px;
    outline: none;
  }
  
  .proof-input-box input:focus { border-color: #15803d; }

  .btn-upload, .btn-send, .btn-print {
    background-color: #15803d;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }
  
  .btn-upload:hover, .btn-send:hover { background-color: #166534; }

  .btn-print {
    background-color: #0d233a;
  }
  .btn-print:hover { background-color: #1e3a8a; }

  .badge-waiting {
    font-size: 11px;
    font-weight: 600;
    color: #d97706;
    background: #fef3c7;
    padding: 4px 8px;
    border-radius: 6px;
  }

  /* ======= RIWAYAT PEKERJAAN (mengikuti halaman Status Lamaran) ======= */
  .history-section {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .history-header {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .history-title {
    font-size: 18px;
    font-weight: 800;
    color: #0d233a;
    margin: 0;
  }

  .history-sub {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
  }

  .history-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .history-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  .history-task-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 10px 0 4px;
    line-height: 1.35;
  }

  .history-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
  }

  .history-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
  }

  .btn-detail {
    padding: 8px 14px;
    background: #f1f5f9;
    color: #334155;
    border: none;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-detail:hover {
    background: #e2e8f0;
  }

  .btn-invoice {
    padding: 8px 14px;
    background: #0d233a;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-invoice:hover {
    background: #1e3a8a;
  }

  .btn-wa-history {
    padding: 8px 14px;
    background: #25d366;
    color: #fff;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
  }

  .btn-wa-history:hover {
    background: #1eb858;
  }

  /* ======= MODAL DETAIL TUGAS (Riwayat) ======= */
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
    max-width: 480px;
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

  :global(body.dark-theme .history-card),
  :global(body.dark-theme .modal-card) {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
  }

  :global(body.dark-theme .history-task-title),
  :global(body.dark-theme .modal-job-title),
  :global(body.dark-theme .modal-title) {
    color: #f1f5f9 !important;
  }

  :global(body.dark-theme .history-meta),
  :global(body.dark-theme .modal-employer),
  :global(body.dark-theme .modal-location),
  :global(body.dark-theme .detail-label) {
    color: #94a3b8 !important;
  }

  :global(body.dark-theme .modal-desc) {
    background: #0f172a !important;
    border-color: #334155 !important;
  }

  :global(body.dark-theme .modal-desc p) {
    color: #cbd5e1 !important;
  }

  :global(body.dark-theme .btn-detail) {
    background: #334155 !important;
    color: #e2e8f0 !important;
  }

  :global(body.dark-theme .modal-close) {
    background: #334155;
    color: #cbd5e1;
  }
</style>