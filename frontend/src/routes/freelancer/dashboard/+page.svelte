<script>
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast } from '$lib/ui/toast.svelte.js';
  import { goto } from '$app/navigation';
  
  // Dummy Data untuk Presentasi
  let earnings = $state(4500000);
  let completedTasks = $state(12);
  let rating = $state(4.9);
  
  // State Gamifikasi
  let currentLevel = $state('Gold Worker');
  let currentXP = $state(2450);
  let targetXP = $state(3000);
  let xpPercentage = $derived((currentXP / targetXP) * 100);

  // State & Data untuk Modal Level Info
  let isLevelModalOpen = $state(false);

  // State untuk Notifikasi Pesan Masuk
  let hasNewMessage = $state(true); // Default true agar indikator langsung menyala saat demo
  let unreadMessageCount = $state(2);
  
  const levelTiers = [
    { id: 'Bronze Worker', xp: '0 - 999', icon: '🥉', reward: 'Akses ke tugas dasar UMKM.' },
    { id: 'Silver Worker', xp: '1.000 - 1.999', icon: '🥈', reward: 'Potongan biaya admin platform sebesar 5%.' },
    { id: 'Gold Worker', xp: '2.000 - 2.999', icon: '🥇', reward: 'Prioritas rekomendasi AI & potongan admin 10%.' },
    { id: 'Platinum', xp: '3.000 - 4.999', icon: '💎', reward: 'Akses tugas VIP UMKM & pencairan dana instan.' },
    { id: 'Diamond', xp: '5.000+', icon: '👑', reward: 'Bebas biaya admin (0%) & Merchandise Eksklusif Kerjain.' }
  ];

  // AI Recommended Jobs (Dummy)
  let recommendedJobs = $state([
    { id: 1, title: 'Desain Logo UMKM Kopi', umkm: 'Kopi Kenangan Senja', price: 'Rp 300.000', match: 98, type: 'Design' },
    { id: 2, title: 'Admin Medsos Instagram', umkm: 'Toko Baju Nabila', price: 'Rp 1.200.000', match: 92, type: 'Social Media' },
    { id: 3, title: 'Input Data Produk Tokopedia', umkm: 'Elektronik Murah', price: 'Rp 150.000', match: 85, type: 'Data Entry' }
  ]);

  function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
  }

  // Fungsi saat tombol "Ambil" diklik (Simulasi penambahan XP)
  function handleTakeJob(job) {
    currentXP += 150;
    
    if (currentXP >= targetXP && currentLevel === 'Gold Worker') {
      currentLevel = 'Platinum';
      targetXP = 5000;
      toast('🎉 Luar biasa! XP Anda cukup dan level naik ke Platinum!', 'success');
    } else {
      toast(`Berhasil mengambil tugas "${job.title}"! (+150 XP)`, 'success');
    }

    recommendedJobs = recommendedJobs.filter(j => j.id !== job.id);
  }

  // Fungsi klik ikon notifikasi -> langsung mengarahkan ke halaman chat
  function openNotifications() {
    hasNewMessage = false;
    unreadMessageCount = 0;
    goto('/freelancer/chat');
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
      <button class="btn-icon notify-btn" onclick={openNotifications} title="Ada pesan baru dari UMKM">
        🔔 
        {#if hasNewMessage}
          <span class="badge-dot animate-ping"></span>
          <span class="badge-dot-solid">{unreadMessageCount}</span>
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
          <h2 class="stat-value">{rating} <span class="text-sm text-slate-400 font-normal">/ 5.0</span></h2>
        </div>
      </div>
    </div>

    <!-- Kolom Kanan (AI Matcher) -->
    <div class="right-col">
      <div class="card ai-card">
        <div class="card-header">
          <h3 class="card-title">🤖 AI Job Matcher</h3>
          <span class="pulse-indicator">Live</span>
        </div>
        <p class="ai-desc">Pekerjaan UMKM yang paling cocok dengan skill Anda.</p>
        
        <div class="job-list">
          {#each recommendedJobs as job (job.id)}
            <div class="job-item">
              <div class="job-info">
                <h4 class="job-title">{job.title}</h4>
                <p class="job-umkm">{job.umkm}</p>
                <span class="job-price">{job.price}</span>
              </div>
              <div class="job-match">
                <div class="match-badge">⚡ {job.match}% Cocok</div>
                <button class="btn-apply" onclick={() => handleTakeJob(job)}>Ambil</button>
              </div>
            </div>
          {:else}
            <p class="empty-jobs">Semua tugas rekomendasi telah diambil! Cek menu Cari Jobs untuk lainnya.</p>
          {/each}
        </div>
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
              <p class="tier-reward">🎁 <strong>Reward:</strong> {tier.reward}</p>
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

  .badge-dot-solid {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #ef4444;
    color: white;
    font-size: 9px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 10px;
    border: 2px solid #ffffff;
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