<script>
  let {
    role = 'freelancer',
    isVerified = false,
    verification = null
  } = $props();

  const submitPath = $derived(role === 'hirer' ? '/hire/verifikasi' : '/freelancer/id');
  const submitLabel = $derived(role === 'hirer' ? 'Ajukan Verifikasi Usaha' : 'Ajukan Verifikasi Identitas');

  const status = $derived(verification?.status ?? 'none');
</script>

<div class="card">
  <h3 class="card-title">🛡️ Status Verifikasi</h3>
  <p class="card-sub">Gunakan lencana terverifikasi untuk membuka akses penuh platform.</p>

  <div class="status-row">
    {#if isVerified}
      <span class="badge badge-green">✓ Terverifikasi</span>
      <p class="status-desc">Akun Anda telah terverifikasi. Anda dapat posting tugas / melamar pekerjaan.</p>
    {:else if status === 'pending'}
      <span class="badge badge-amber">⏳ Menunggu Review Admin</span>
      <p class="status-desc">Data verifikasi sedang diperiksa tim Kerjain. Proses maksimal 1x24 jam.</p>
    {:else if status === 'rejected'}
      <span class="badge badge-red">✕ Ditolak</span>
      <p class="status-desc">
        Pengajuan sebelumnya ditolak:
        <span class="reject-reason">"{verification.admin_note || 'Data kurang lengkap.'}"</span>
        Silakan perbaiki dan ajukan ulang.
      </p>
    {:else}
      <span class="badge badge-red">⚠️ Belum Terverifikasi</span>
      <p class="status-desc">Selesaikan verifikasi untuk membuka akses penuh platform.</p>
    {/if}
  </div>

  {#if !isVerified}
    <a href={submitPath} class="btn-submit">{submitLabel}</a>
  {/if}
</div>

<style>
  .card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .card-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .card-sub {
    font-size: 12px;
    color: #64748b;
    margin: -6px 0 0;
  }

  .status-row {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .badge {
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-block;
  }

  .badge-green {
    background: #dcfce7;
    color: #166534;
  }

  .badge-amber {
    background: #fef3c7;
    color: #92400e;
  }

  .badge-red {
    background: #fee2e2;
    color: #991b1b;
  }

  .status-desc {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
  }

  .reject-reason {
    color: #dc2626;
    font-weight: 600;
  }

  .btn-submit {
    display: inline-block;
    text-align: center;
    background: #15803d;
    color: #ffffff;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
  }

  .btn-submit:hover {
    background: #166534;
  }

  :global(body.dark-theme .card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .card-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .card-sub),
  :global(body.dark-theme .status-desc) {
    color: #94a3b8 !important;
  }
</style>