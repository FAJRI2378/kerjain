<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';
  import { goto } from '$app/navigation';

  // Statistik Real dari Database
  let earnings = $state(0);
  let completedTasks = $state(0);
  let rating = $state(0);
  let totalReviews = $state(0);

  // Rekomendasi Lowongan Terbuka (real dari feed tugas aktif)
  let recommendedJobs = $state([]);
  let loading = $state(true);
  let applyingId = $state(null);

  let unverifiedGate = $derived(!!(auth.user && !auth.user.is_verified));

  // State Gamifikasi (turunan dari jumlah tugas selesai)
  let currentLevel = $state('Bronze Worker');
  let currentXP = $state(0);
  let targetXP = $state(1000);
  let xpPercentage = $derived(Math.min((currentXP / targetXP) * 100, 100));

  // State & Data untuk Modal Level Info
  let isLevelModalOpen = $state(false);

  // State untuk Notifikasi Pesan Masuk
  let hasNewMessage = $state(false);

  const levelTiers = [
    { id: 'Bronze Worker', xp: '0 - 9 tugas', icon: '🥉' },
    { id: 'Silver Worker', xp: '10 - 24 tugas', icon: '🥈' },
    { id: 'Gold Worker', xp: '25 - 49 tugas', icon: '🥇' },
    { id: 'Platinum', xp: '50 - 99 tugas', icon: '💎' },
    { id: 'Diamond', xp: '100+ tugas', icon: '👑' }
  ];

  const LEVEL_THRESHOLDS = [
    { id: 'Diamond', minTasks: 100, xpTarget: 100000 },
    { id: 'Platinum', minTasks: 50, xpTarget: 50000 },
    { id: 'Gold Worker', minTasks: 25, xpTarget: 25000 },
    { id: 'Silver Worker', minTasks: 10, xpTarget: 10000 },
    { id: 'Bronze Worker', minTasks: 0, xpTarget: 1000 }
  ];

  function updateLevel(totalTasks) {
    for (const tier of LEVEL_THRESHOLDS) {
      if (totalTasks >= tier.minTasks) {
        currentLevel = tier.id;
        targetXP = tier.xpTarget;
        break;
      }
    }
    currentXP = Math.min(totalTasks * 100, targetXP);
  }

  async function loadDashboard() {
    loading = true;
    try {
      const [dashRes, jobRes] = await Promise.all([
        api.get('/api/freelancer/dashboard'),
        api.get('/api/tasks?per_page=5')
      ]);
      earnings = dashRes.data?.balance ?? 0;
      completedTasks = dashRes.data?.completed_tasks ?? 0;
      rating = dashRes.data?.average_rating ?? 0;
      totalReviews = dashRes.data?.total_reviews ?? 0;
      recommendedJobs = jobRes.data ?? [];
      updateLevel(completedTasks);
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat data dashboard.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(loadDashboard);

  function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(number) || 0);
  }

  // Ambil tugas real dari feed dan kirim lamaran
  async function handleTakeJob(job) {
    applyingId = job.id;
    try {
      await api.post(`/api/tasks/${job.id}/apply`, {});
      toast(`Lamaran untuk "${job.title}" terkirim! Menunggu konfirmasi UMKM.`, 'success');
      recommendedJobs = recommendedJobs.filter(j => j.id !== job.id);
    } catch (err) {
      if (err.status === 409) {
        recommendedJobs = recommendedJobs.filter(j => j.id !== job.id);
        toast(err.message || 'Kamu sudah melamar tugas ini.', 'info');
      } else {
        toast(errorMessage(err, 'Gagal mengirim lamaran.'), 'error');
      }
    } finally {
      applyingId = null;
    }
  }

  // Fungsi klik ikon notifikasi -> langsung mengarahkan ke halaman kontak UMKM
  function openNotifications() {
    hasNewMessage = false;
    goto('/freelancer/kontak');
  }
</script>

<div class="dashboard-container font-sans">
  <!-- Header Section -->
  <header class="dash-header">
    <div>
      <h1 class="greeting">Halo, {auth.user?.name || 'Freelancer'}! 👋</h1>
      <p class="subtitle">Siap untuk membantu UMKM hari ini?</p>
    </div>
    <div class="header-actions">
      <!-- Tombol Notifikasi Pesan Interaktif -->
      <button class="btn-icon notify-btn" onclick={openNotifications} title="Buka pesan">
        🔔 
        {#if hasNewMessage}
          <span class="badge-dot animate-ping"></span>
        {/if}
      </button>
    </div>
  </header>

  <div class="grid-layout">
    <!-- Kolom Kiri (Main Stats) -->
    <div class="left-col">
      <!-- Gamification Card -->
      <div class="card gamification-card gradient-bg">
        <div class="level-header-wrapper">
          <div class="level-info">
            <div class="badge-icon">🏆</div>
            <div>
              <h3 class="level-title">Level: {currentLevel}</h3>
              <p class="xp-text">{currentXP} / {targetXP} XP menuju level berikutnya</p>
            </div>
          </div>
          <!-- Tombol Cek Detail Level -->
          <button class="btn-level-detail" onclick={() => isLevelModalOpen = true}>
            ℹ️ Detail Level
          </button>
        </div>
        <div class="progress-track">
          <div class="progress-fill" style="width: {xpPercentage}%"></div>
        </div>
      </div>

      <!-- Stats Grid -->
      {#if loading}
        <div class="stats-loading">Memuat data dashboard...</div>
      {:else}
      <div class="stats-grid">
        <div class="card stat-card">
          <span class="stat-icon bg-green">💰</span>
          <p class="stat-label">Total Pendapatan</p>
          <h2 class="stat-value">{formatRupiah(earnings)}</h2>
        </div>
        <div class="card stat-card">
          <span class="stat-icon bg-blue">📋</span>
          <p class="stat-label">Tugas Selesai</p>
          <h2 class="stat-value">{completedTasks} <span class="text-sm text-slate-400 font-normal">Tugas</span></h2>
        </div>
        <div class="card stat-card">
          <span class="stat-icon bg-yellow">⭐</span>
          <p class="stat-label">Rating Pekerja</p>
          <h2 class="stat-value">{Number(rating).toFixed(1)} <span class="text-sm text-slate-400 font-normal">/ 5.0 ({totalReviews} Review)</span></h2>
        </div>
      </div>
      {/if}
    </div>

    <!-- Kolom Kanan (Job Matcher) -->
    <div class="right-col">
      <div class="card ai-card">
        <div class="card-header">
          <h3 class="card-title">🤖 Job Matcher</h3>
          <span class="pulse-indicator">Live</span>
        </div>
        <p class="ai-desc">Lowongan tugas UMKM terbaru yang terbuka untuk Anda.</p>

        {#if loading}
          <p class="empty-jobs">Memuat rekomendasi tugas...</p>
        {:else if recommendedJobs.length === 0}
          <p class="empty-jobs">Belum ada tugas terbuka saat ini. Cek menu Cari Jobs untuk mencari tugas lainnya.</p>
        {:else}
        <div class="job-list">
          {#each recommendedJobs as job (job.id)}
            <div class="job-item">
              <div class="job-info">
                <h4 class="job-title">{job.title}</h4>
                <p class="job-umkm">{job.owner?.name ?? '-'} • {job.category?.name ?? 'Tugas Mikro'}</p>
                <span class="job-price">{formatRupiah(job.budget)}</span>
              </div>
              <div class="job-match">
                <div class="match-badge">📍 {job.location ?? 'Lokasi Lokal'}</div>
                {#if unverifiedGate}
                  <a href="/freelancer/id" class="btn-apply btn-apply-blocked" title="Verifikasi identitas untuk melamar">
                    Verifikasi
                  </a>
                {:else}
                  <button class="btn-apply" disabled={applyingId === job.id} onclick={() => handleTakeJob(job)}>
                    {applyingId === job.id ? 'Mengirim...' : 'Ambil'}
                  </button>
                {/if}
              </div>
            </div>
          {/each}
        </div>
        {/if}
      </div>
    </div>
  </div>
</div>

<!-- Modal Informasi Level & Reward -->
{#if isLevelModalOpen}
  <div class="modal-backdrop" onclick={() => isLevelModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header">
        <div class="modal-title-group">
          <h2 class="modal-title">Tingkat Level & Reward 🏆</h2>
          <p class="modal-sub">Kumpulkan XP dengan menyelesaikan tugas UMKM dan dapatkan keuntungannya!</p>
        </div>
        <button class="btn-close" onclick={() => isLevelModalOpen = false}>✕</button>
      </div>

      <div class="tiers-list">
        {#each levelTiers as tier}
          <!-- Highlight jika ini adalah level user sekarang -->
          <div class="tier-item {tier.id === currentLevel ? 'active-tier' : ''}">
            <div class="tier-icon">{tier.icon}</div>
            <div class="tier-content">
              <div class="tier-head">
                <h4 class="tier-name">{tier.id}</h4>
                <span class="tier-xp">{tier.xp} XP</span>
              </div>
              <p class="tier-reward">Selesaikan tugas secara konsisten untuk naik level dan membangun kepercayaan UMKM.</p>
            </div>
            {#if tier.id === currentLevel}
              <div class="current-badge">Kamu di sini</div>
            {/if}
          </div>
        {/each}
      </div>

      <div class="modal-actions">
        <button class="btn-primary" onclick={() => isLevelModalOpen = false}>Mengerti</button>
      </div>
    </div>
  </div>
{/if}

<style>
  .dashboard-container {
    padding: 32px 24px;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 32px;
  }

  .dash-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .greeting {
    font-size: 28px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.02em;
  }

  .subtitle {
    color: #64748b;
    margin: 0;
    font-size: 14px;
  }

  .btn-icon {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 18px;
    cursor: pointer;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s;
  }
  .btn-icon:hover {
    transform: scale(1.05);
  }

  .badge-dot {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 10px;
    height: 10px;
    background: #ef4444;
    border-radius: 50%;
  }

  .grid-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
  }

  @media (min-width: 992px) {
    .grid-layout {
      grid-template-columns: 1.2fr 1fr;
    }
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
  }

  /* Gamification */
  .gradient-bg {
    background: linear-gradient(135deg, #0d233a 0%, #1a365d 100%);
    color: white;
    border: none;
    margin-bottom: 24px;
  }

  .level-header-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
  }

  .level-info {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .badge-icon {
    font-size: 40px;
    background: rgba(255,255,255,0.1);
    padding: 12px;
    border-radius: 16px;
  }

  .level-title {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 800;
  }

  .xp-text {
    margin: 0;
    font-size: 13px;
    color: #94a3b8;
  }

  .btn-level-detail {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-level-detail:hover {
    background: rgba(255,255,255,0.25);
  }

  .progress-track {
    width: 100%;
    height: 10px;
    background: rgba(255,255,255,0.1);
    border-radius: 8px;
    overflow: hidden;
  }

  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    border-radius: 8px;
    transition: width 1s ease-in-out;
  }

  /* Stats Grid */
  .stats-loading {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
  }

  .stat-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .stat-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    font-size: 18px;
  }

  .bg-green { background: #dcfce7; color: #166534; }
  .bg-blue { background: #dbeafe; color: #1e3a8a; }
  .bg-yellow { background: #fef3c7; color: #92400e; }

  .stat-label {
    margin: 0;
    font-size: 12.5px;
    font-weight: 700;
    color: #64748b;
  }

  .stat-value {
    margin: 0;
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
  }

  /* AI Card */
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }

  .card-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0d233a;
  }

  .pulse-indicator {
    font-size: 10px;
    font-weight: 800;
    color: #ef4444;
    background: #fee2e2;
    padding: 2px 8px;
    border-radius: 12px;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
  }

  .ai-desc {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 20px;
  }

  .job-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .job-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    background: #f8fafc;
    transition: border-color 0.2s;
  }

  .job-item:hover {
    border-color: #10b981;
  }

  .job-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .job-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
  }

  .job-umkm {
    margin: 0;
    font-size: 11px;
    color: #64748b;
  }

  .job-price {
    font-size: 13px;
    font-weight: 800;
    color: #15803d;
    margin-top: 4px;
  }

  .job-match {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
  }

  .match-badge {
    background: #dcfce7;
    color: #15803d;
    font-size: 10px;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 6px;
  }

  .btn-apply {
    background: #0d233a;
    color: white;
    border: none;
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }

  .btn-apply:hover {
    background: #1e3a8a;
  }

  .btn-apply-blocked {
    background: #d97706;
    text-decoration: none;
    display: inline-block;
  }

  .btn-apply-blocked:hover {
    background: #b45309;
  }

  .empty-jobs {
    font-size: 13px;
    color: #64748b;
    text-align: center;
    padding: 16px;
    margin: 0;
  }

  /* ---------------- Modal & Tiers Styles ---------------- */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(13, 35, 58, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 100;
    animation: fadeIn 0.2s ease-out;
  }

  .modal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    width: 100%;
    max-width: 550px;
    padding: 28px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 24px;
  }

  .modal-title {
    font-size: 20px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
  }

  .modal-sub {
    font-size: 13px;
    color: #64748b;
    margin: 0;
  }

  .btn-close {
    background: none;
    border: none;
    font-size: 20px;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
  }
  .btn-close:hover { color: #0d233a; }

  .tiers-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .tier-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    position: relative;
    overflow: hidden;
  }

  .tier-item.active-tier {
    background: #f0fdf4;
    border-color: #22c55e;
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.1);
  }

  .tier-icon {
    font-size: 32px;
    background: #ffffff;
    padding: 8px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .tier-content {
    flex: 1;
  }

  .tier-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
  }

  .tier-name {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
  }

  .tier-xp {
    font-size: 11px;
    font-weight: 800;
    color: #15803d;
    background: #dcfce7;
    padding: 4px 8px;
    border-radius: 6px;
  }

  .tier-reward {
    margin: 0;
    font-size: 12px;
    color: #475569;
    line-height: 1.4;
  }

  .current-badge {
    position: absolute;
    top: 12px;
    right: -24px;
    background: #15803d;
    color: white;
    font-size: 9px;
    font-weight: 800;
    padding: 4px 24px;
    transform: rotate(45deg);
  }

  .modal-actions {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
  }

  .btn-primary {
    background: #15803d;
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn-primary:hover {
    background: #166534;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .btn-icon) {
    background: #1e293b;
    border-color: #334155;
  }
  :global(body.dark-theme .card-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .pulse-indicator) {
    background: rgba(239, 68, 68, 0.15) !important;
    color: #f87171 !important;
  }
  :global(body.dark-theme .tier-item) {
    background: #0f172a;
    border-color: #334155;
  }
  :global(body.dark-theme .tier-item.active-tier) {
    background: rgba(21, 128, 61, 0.15);
    border-color: #15803d;
  }
  :global(body.dark-theme .tier-icon) {
    background: #1e293b;
  }
  :global(body.dark-theme .tier-name) {
    color: #ffffff;
  }
  :global(body.dark-theme .tier-reward) {
    color: #cbd5e1;
  }
  :global(body.dark-theme .job-item) {
    background: #0f172a;
    border-color: #334155;
  }
  :global(body.dark-theme .job-title) {
    color: #ffffff;
  }
</style>