<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let jobs = $state([]);
  let loading = $state(true);
  let completingId = $state(null);

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/hire/jobs');
      jobs = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat pekerjaan.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  const badge = $derived((status) => {
    if (status === 'reviewing') return { label: '🔍 Butuh Persetujuan', cls: 'badge-amber' };
    if (status === 'in_progress') return { label: '⚡ Sedang Dikerjakan', cls: 'badge-blue' };
    if (status === 'completed') return { label: '✅ Selesai & Lunas', cls: 'badge-green' };
    if (status === 'approved') return { label: '⏳ Menunggu Freelancer', cls: 'badge-slate' };
    if (status === 'pending') return { label: '⏳ Menunggu Moderasi', cls: 'badge-amber' };
    return { label: status, cls: 'badge-slate' };
  });

  async function approveWork(jobId, title) {
    if (!confirm('Apakah Anda yakin menyetujui hasil pengerjaan ini? Dana Escrow akan ditransfer ke Freelancer.')) return;
    completingId = jobId;
    try {
      await api.post(`/api/tasks/${jobId}/complete`, {});
      jobs = jobs.map((j) => (j.id === jobId ? { ...j, status: 'completed' } : j));
      toast(`Pekerjaan "${title}" selesai! Dana berhasil dicairkan ke Freelancer.`, 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyetujui pekerjaan.'), 'error');
    } finally {
      completingId = null;
    }
  }
</script>

<div class="jobs-manage-page font-sans">
  <!-- Header -->
  <div class="page-header">
    <h1 class="page-title">Kelola Pekerjaan Saya</h1>
    <p class="page-sub">Review bukti pengerjaan tugas dari freelancer dan konfirmasi pencairan dana.</p>
  </div>

  <!-- List Jobs -->
  {#if loading}
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Memuat pekerjaan...</p>
    </div>
  {:else if jobs.length === 0}
    <div class="empty-state">
      <p>Belum ada pekerjaan. Buat tugas baru untuk mulai.</p>
    </div>
  {:else}
    <div class="jobs-list">
      {#each jobs as job (job.id)}
        {@const b = badge(job.status)}
        <div class="card job-card">
          <div class="job-info-col">
            <div class="job-meta-top">
              <span class="job-id">#{job.id}</span>
              <span class={`status-badge ${b.cls}`}>{b.label}</span>
            </div>
            <h3 class="job-title">{job.title}</h3>
            <p class="job-worker">Freelancer: <strong>{job.worker?.name ?? 'Belum ada'}</strong></p>
            {#if job.proof_url}
              <a href={job.proof_url} target="_blank" rel="noreferrer" class="proof-link">
                📎 Lihat Bukti Pekerjaan Freelancer
              </a>
            {/if}
          </div>

          <div class="job-action-col">
            <div class="contract-value-box">
              <span class="contract-label">Nilai Kontrak</span>
              <span class="contract-amount">{formatRupiah(job.budget)}</span>
            </div>

            {#if job.status === 'reviewing'}
              <button
                onclick={() => approveWork(job.id, job.title)}
                disabled={completingId === job.id}
                class="btn-approve"
              >
                {completingId === job.id ? 'Memproses...' : 'Approve & Cairkan'}
              </button>
            {:else if job.status === 'in_progress'}
              <span class="status-note">Menunggu Upload Bukti</span>
            {:else if job.status === 'completed'}
              <span class="status-note success">✓ Selesai</span>
            {:else}
              <span class="status-note">Menunggu proses</span>
            {/if}
          </div>
        </div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .jobs-manage-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
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

  .loading-state, .empty-state {
    text-align: center;
    padding: 48px 24px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    color: #64748b;
    font-size: 13.5px;
  }

  .jobs-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .job-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  @media (min-width: 768px) {
    .job-card {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .job-info-col {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
  }

  .job-meta-top {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .job-id {
    font-family: monospace;
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
  }

  .status-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
  }

  .badge-amber { background: #fef3c7; color: #92400e; }
  .badge-blue { background: #dbeafe; color: #1e40af; }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-slate { background: #f1f5f9; color: #475569; }

  .job-title {
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
    margin: 2px 0;
  }

  .job-worker {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
  }

  .job-worker strong {
    color: #334155;
  }

  .proof-link {
    font-size: 12px;
    color: #15803d;
    font-weight: 700;
    text-decoration: none;
    margin-top: 4px;
    display: inline-block;
  }

  .proof-link:hover {
    text-decoration: underline;
  }

  .job-action-col {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
  }

  @media (min-width: 768px) {
    .job-action-col {
      padding-top: 0;
      border-top: none;
      justify-content: flex-end;
    }
  }

  .contract-value-box {
    text-align: left;
  }

  @media (min-width: 768px) {
    .contract-value-box {
      text-align: right;
    }
  }

  .contract-label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .contract-amount {
    font-size: 16px;
    font-weight: 800;
    color: #15803d;
  }

  .btn-approve {
    background: #15803d;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
    transition: background 0.2s;
    white-space: nowrap;
  }

  .btn-approve:hover:not(:disabled) {
    background: #166534;
  }

  .btn-approve:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .status-note {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    font-style: italic;
    white-space: nowrap;
  }

  .status-note.success {
    color: #15803d;
    font-style: normal;
    font-weight: 700;
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .card),
  :global(body.dark-theme .loading-state),
  :global(body.dark-theme .empty-state) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .job-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .job-worker),
  :global(body.dark-theme .job-id),
  :global(body.dark-theme .contract-label) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .job-action-col) {
    border-color: #334155 !important;
  }
  :global(body.dark-theme .badge-amber) { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
  :global(body.dark-theme .badge-blue) { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
  :global(body.dark-theme .badge-green) { background: rgba(34, 197, 94, 0.15); color: #4ade80; }
  :global(body.dark-theme .badge-slate) { background: rgba(100, 116, 139, 0.15); color: #cbd5e1; }
</style>