<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let stats = $state({
    escrowBalance: 0,
    totalSpent: 0,
    activeJobsCount: 0,
    applicantsCount: 0
  });

  let activeJobs = $state([]);
  let loading = $state(true);

  onMount(async () => {
    try {
      const res = await api.get('/api/hire/dashboard');
      const d = res.data;
      stats = {
        escrowBalance: d.escrow_balance ?? 0,
        totalSpent: d.total_spent ?? 0,
        activeJobsCount: d.active_jobs_count ?? 0,
        applicantsCount: d.applicants_count ?? 0
      };
      activeJobs = d.active_jobs ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat dashboard.'), 'error');
    } finally {
      loading = false;
    }
  });

  const businessName = $derived(auth.user?.business_profile?.business_name ?? auth.user?.name ?? 'UMKM');
  const statusBadge = $derived((status) =>
    status === 'reviewing'
      ? { label: '🔍 Perlu Di-Review', cls: 'amber-badge' }
      : { label: '⚡ Berjalan', cls: 'green-badge' }
  );
</script>

<div class="dashboard-page">
  <!-- Top Banner / Header Greeting -->
  <div class="dashboard-banner">
    <div class="banner-content">
      <div class="portal-tag">
        <span class="pulse-dot"></span>
        BUSINESS DASHBOARD
      </div>
      <h1 class="welcome-title">{businessName} 🏪</h1>
      <p class="welcome-sub">Kelola pengerjaan proyek, alokasi dana escrow, dan review pelamar.</p>
    </div>

    <a href="/hire/jobs/create" class="btn-cta">
      <span>➕</span> Buat Tugas Baru
    </a>
  </div>

  {#if loading}
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Memuat data dashboard...</p>
    </div>
  {:else}
    <!-- Stats Cards Grid -->
    <div class="stats-grid">
      <!-- Escrow Balance Card -->
      <div class="stat-card highlight-card">
        <div class="stat-header">
          <span class="stat-label">Saldo Anda </span>
          <span class="stat-icon">🛡️</span>
        </div>
        <div class="stat-value text-navy">{formatRupiah(stats.escrowBalance)}</div>
        <button class="btn-topup">
          Top Up Saldo
        </button>
      </div>

      <!-- Active Jobs Card -->
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Tugas Tayang</span>
          <span class="stat-icon">📌</span>
        </div>
        <div class="stat-value">{stats.activeJobsCount}</div>
        <div class="stat-footer">
          <span class="sub-badge green-badge">Menerima Pelamar</span>
        </div>
      </div>

      <!-- Pending Applicants Card -->
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Total Pelamar</span>
          <span class="stat-icon">👥</span>
        </div>
        <div class="stat-value">{stats.applicantsCount}</div>
        <div class="stat-footer">
          <span class="sub-badge amber-badge">Butuh Review</span>
        </div>
      </div>

      <!-- Total Spent Card -->
      <div class="stat-card">
        <div class="stat-header">
          <span class="stat-label">Total Pengeluaran</span>
          <span class="stat-icon">📈</span>
        </div>
        <div class="stat-value text-dark">{formatRupiah(stats.totalSpent)}</div>
        <div class="stat-footer">
          <span class="sub-info">Akumulasi Proyek</span>
        </div>
      </div>
    </div>

    <!-- Active Jobs Overview -->
    <div class="section-card">
      <div class="section-header">
        <div>
          <h2 class="section-title">Status Pengerjaan Tugas</h2>
          <p class="section-sub">Pantau progres pengerjaan freelancer dan setujui bukti hasil kerja.</p>
        </div>
        <a href="/hire/jobs" class="link-view-all">Kelola Semua →</a>
      </div>

      {#if activeJobs.length === 0}
        <div class="empty-state">
          <p>Belum ada tugas berjalan. Buat tugas baru untuk mulai mendatangkan pelamar!</p>
        </div>
      {:else}
        <div class="jobs-list">
          {#each activeJobs as job (job.id)}
            {@const badge = statusBadge(job.status)}
            <div class="job-item">
              <div class="job-main">
                <span class="job-id">#{job.id}</span>
                <h3 class="job-title">{job.title}</h3>
                <div class="job-meta">
                  <span class="meta-item">Pelamar: <strong>{job.applicants_count ?? 0} Freelancer</strong></span>
                  <span class="meta-dot">•</span>
                  <span class="meta-item">Budget: <strong class="text-green">{formatRupiah(job.budget)}</strong></span>
                </div>
              </div>

              <div class="job-action-col">
                <span class={`status-badge ${badge.cls}`}>
                  {badge.label}
                </span>
                <a href="/hire/jobs" class="btn-detail">
                  Detail
                </a>
              </div>
            </div>
          {/each}
        </div>
      {/if}
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

  * {
    box-sizing: border-box;
  }

  .dashboard-page {
    max-width: 1240px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  /* Banner Section */
  .dashboard-banner {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  @media (min-width: 640px) {
    .dashboard-banner {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .portal-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    color: #0d233a;
    letter-spacing: 0.08em;
    margin-bottom: 6px;
  }

  .pulse-dot {
    width: 8px;
    height: 8px;
    background-color: #2563eb;
    border-radius: 50%;
    display: inline-block;
  }

  .welcome-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  .welcome-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
  }

  .btn-cta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background-color: #15803d;
    color: #ffffff;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    text-decoration: none;
    text-align: center;
    white-space: nowrap;
    transition: background-color 0.2s;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);
  }

  .btn-cta:hover {
    background-color: #166534;
  }

  /* Loading State */
  .loading-state {
    text-align: center;
    padding: 60px 0;
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
  }

  /* Stats Grid & Cards */
  .stats-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  @media (min-width: 640px) {
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 1024px) {
    .stats-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }

  .stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px 22px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  .stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .stat-label {
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    letter-spacing: 0.05em;
    text-transform: uppercase;
  }

  .stat-icon {
    font-size: 18px;
  }

  .stat-value {
    font-size: 26px;
    font-weight: 800;
    color: #0d233a;
    line-height: 1.1;
    margin-bottom: 12px;
  }

  .stat-value.text-navy {
    color: #0d233a;
  }

  .stat-value.text-dark {
    color: #334155;
  }

  .btn-topup {
    width: 100%;
    padding: 8px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #0d233a;
    font-weight: 700;
    font-size: 11.5px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-topup:hover {
    background: #e2e8f0;
  }

  .sub-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-block;
  }

  .green-badge {
    background-color: #dcfce7;
    color: #166534;
  }

  .amber-badge {
    background-color: #fef3c7;
    color: #92400e;
  }

  .sub-info {
    font-size: 11px;
    color: #64748b;
  }

  /* Section Card */
  .section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  @media (max-width: 639px) {
    .dashboard-banner,
    .section-card {
      padding: 20px;
    }
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
  }

  .section-title {
    font-size: 18px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
  }

  .section-sub {
    font-size: 13px;
    color: #64748b;
    margin: 0;
  }

  .link-view-all {
    font-size: 13px;
    font-weight: 700;
    color: #15803d;
    text-decoration: none;
  }

  .link-view-all:hover {
    text-decoration: underline;
  }

  .empty-state {
    text-align: center;
    padding: 40px 0;
    color: #94a3b8;
    font-size: 14px;
  }

  /* Jobs List Items */
  .jobs-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .job-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: border-color 0.2s;
  }

  .job-item:hover {
    border-color: #cbd5e1;
  }

  @media (min-width: 640px) {
    .job-item {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .job-id {
    font-family: monospace;
    font-size: 11px;
    font-weight: 700;
    color: #0d233a;
  }

  .job-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 2px 0 6px;
  }

  .job-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
    color: #64748b;
  }

  .meta-item strong {
    color: #334155;
  }

  .text-green {
    color: #15803d !important;
  }

  .meta-dot {
    color: #cbd5e1;
  }

  .job-action-col {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  @media (min-width: 640px) {
    .job-action-col {
      justify-content: flex-end;
    }
  }

  .status-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 6px;
    white-space: nowrap;
  }

  .btn-detail {
    background-color: #0d233a;
    color: #ffffff;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color 0.2s;
  }

  .btn-detail:hover {
    background-color: #1e293b;
  }
</style>