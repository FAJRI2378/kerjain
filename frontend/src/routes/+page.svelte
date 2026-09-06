<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';

  const PER_PAGE = 6;
  const VISIBLE_CHIPS = 8;

  let searchQuery = $state("");
  let activeSearch = $state("");
  let selectedSlug = $state("all");
  let categories = $state([]);
  let jobs = $state([]);
  let page = $state(1);
  let lastPage = $state(1);
  let total = $state(0);
  let loading = $state(true);
  let loadError = $state(null);
  let categorySelectValue = $state("");
  let isDarkMode = $state(false); // State untuk mendeteksi mode saat ini

  let visibleCategories = $derived(categories.slice(0, VISIBLE_CHIPS));
  let hiddenCategories = $derived(categories.slice(VISIBLE_CHIPS));

  let pages = $derived.by(() => {
    if (lastPage <= 7) return Array.from({ length: lastPage }, (_, i) => i + 1);
    const window = [...new Set([1, 2, lastPage - 1, lastPage, page, page - 1, page + 1])]
      .filter((p) => p >= 1 && p <= lastPage)
      .sort((a, b) => a - b);
    const out = [];
    let prev = 0;
    for (const p of window) {
      if (prev && p - prev > 1) out.push("...");
      out.push(p);
      prev = p;
    }
    return out;
  });

  async function loadCategories() {
    const res = await api.get('/api/categories');
    categories = res.data ?? [];
  }

  async function loadJobs() {
    loading = true;
    loadError = null;
    try {
      const params = new URLSearchParams({ per_page: String(PER_PAGE), page: String(page) });
      if (activeSearch.trim()) params.set('search', activeSearch.trim());
      if (selectedSlug !== 'all') params.set('category', selectedSlug);
      const res = await api.get(`/api/jobs?${params.toString()}`);
      jobs = res.data ?? [];
      page = res.meta?.current_page ?? 1;
      lastPage = res.meta?.last_page ?? 1;
      total = res.meta?.total ?? jobs.length;
    } catch (err) {
      jobs = [];
      total = 0;
      loadError = err.message || 'Gagal memuat tugas.';
    } finally {
      loading = false;
    }
  }

  function handleSearch(event) {
    event.preventDefault();
    activeSearch = searchQuery;
    page = 1;
    loadJobs();
  }

  function selectCategory(slug) {
    selectedSlug = slug;
    categorySelectValue = "";
    page = 1;
    loadJobs();
  }

  function goToPage(p) {
    if (p < 1 || p > lastPage || p === page) return;
    page = p;
    loadJobs();
  }

  // Load preferensi tema pengguna saat komponen dimuat
  onMount(() => {
    const savedTheme = localStorage.getItem('kerjain-theme');
    if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      isDarkMode = true;
      document.body.classList.add('dark-theme');
    }

    Promise.all([loadCategories(), loadJobs()]).catch(() => {});
  });

  // Fungsi mengubah tema
  function toggleTheme() {
    isDarkMode = !isDarkMode;
    if (isDarkMode) {
      document.body.classList.add('dark-theme');
      localStorage.setItem('kerjain-theme', 'dark');
    } else {
      document.body.classList.remove('dark-theme');
      localStorage.setItem('kerjain-theme', 'light');
    }
  }
</script>

<div class="page">
  <!-- Header Navbar -->
  <header class="site-header">
    <div class="shell header-row">
      <a href="/" class="brand">
        <div class="brand-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
        </div>
        <div class="brand-text">
          <span class="brand-name">KERJAIN</span>
          <span class="brand-tagline">Kerja kecil, dampak besar</span>
        </div>
      </a>

      <nav class="site-nav">
        <!-- Tombol Toggle Tema -->
        <button class="theme-toggle" onclick={toggleTheme} aria-label="Toggle Dark Mode">
          {#if isDarkMode}
            ☀️
          {:else}
            🌙
          {/if}
        </button>

        <a href="/register" class="btn-primary-header">
          <span class="plus-icon">+</span> Buka Lowongan / Cari Pekerjaan
        </a>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="shell hero-container">
      <div class="hero-content">
        <h1 class="hero-title">
          Temukan kerja yang dekat dengan <span class="highlight-text">keahlianmu.</span>
        </h1>
        
        <p class="hero-subtitle">
          Ambil pekerjaan singkat dari UMKM sekitar, dapatkan pengalaman nyata, dan bantu bisnis lokal tumbuh.
        </p>

        <form class="search-box" onsubmit={handleSearch}>
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="text" 
            placeholder="Cari Foto, Excel, Desain..." 
            bind:value={searchQuery}
          />
          <button class="btn-search" type="submit">Cari Tugas</button>
        </form>
      </div>

      <div class="hero-stats-panel">
        <div class="stats-divider"></div>
        <div class="stats-body">
          <div class="stat-number">1000<span class="plus">+</span></div>
          <p class="stat-label">tugas<br />siap dikerjakan</p>
          <div class="quote-box">
            <p class="quote-text">“Peluang pertama bisa dimulai dari jarak terdekat.”</p>
            <div class="quote-footer">
              <span class="check-icon">✓</span>
              <span class="check-text">Aman & transparan</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Content / Task Listings -->
  <main class="main-content">
    <div class="shell">
      <div class="section-label">PELUANG TERBARU</div>
      
      <div class="tasks-header">
        <h2 class="section-title">Tugas di sekitarmu</h2>
        <span class="task-count">{total} tugas tersedia</span>
      </div>

      <!-- Category Filter Tabs -->
      <div class="category-tabs">
        <button 
          class="tab-btn {selectedSlug === 'all' ? 'active' : ''}" 
          onclick={() => selectCategory('all')}
        >
          Semua
        </button>
        {#each visibleCategories as cat}
          <button 
            class="tab-btn {selectedSlug === cat.slug ? 'active' : ''}" 
            onclick={() => selectCategory(cat.slug)}
          >
            {cat.name}
          </button>
        {/each}

        {#if hiddenCategories.length > 0}
          <div class="category-dropdown-wrap">
            <select
              class="category-select"
              bind:value={categorySelectValue}
              onchange={() => {
                if (categorySelectValue) {
                  selectCategory(categorySelectValue);
                }
              }}
            >
              <option value="">Kategori Lainnya ▾</option>
              {#each hiddenCategories as cat}
                <option value={cat.slug}>{cat.name}</option>
              {/each}
            </select>
          </div>
        {/if}
      </div>

      {#if loading}
        <div class="loading-state">
          <div class="spinner"></div>
          <p>Memuat tugas...</p>
        </div>
      {:else if loadError}
        <div class="error-state">
          <p>{loadError}</p>
          <button class="btn-action" onclick={loadJobs}>Coba Lagi</button>
        </div>
      {:else if jobs.length === 0}
        <div class="empty-state">
          <p>Belum ada tugas yang cocok.</p>
        </div>
      {:else}
        <!-- Task Cards Grid -->
        <div class="tasks-grid">
          {#each jobs as task (task.id)}
            <div class="task-card">
              <div class="card-header">
                <span class="badge-cat">{task.category?.name ?? 'Tugas Mikro'}</span>
                <span class="status-indicator">
                  <span class="dot"></span> Buka
                </span>
              </div>

              <h3 class="task-title">{task.title}</h3>

              <div class="card-meta">
                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                  {task.owner?.name ?? '-'}
                </span>
                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                  {task.location ?? 'Lokasi Lokal'}
                </span>
              </div>

              <p class="task-desc">{task.description}</p>

              <div class="tags-container">
                <span class="tag-pill">#{task.category?.name ?? 'Tugas Mikro'}</span>
              </div>

              <div class="card-footer">
                <div class="reward-info">
                  <span class="reward-label">IMBALAN</span>
                  <div class="reward-value">
                    {formatRupiah(task.budget)}
                    {#if task.deadline}
                      <span class="duration-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        {task.deadline}
                      </span>
                    {/if}
                  </div>
                </div>

                <a class="btn-action" href="/login">
                  Ambil Tugas <span class="arrow">→</span>
                </a>
              </div>
            </div>
          {/each}
        </div>

        {#if lastPage > 1}
          <div class="pagination-container">
            <span class="pagination-info">Halaman {page} dari {lastPage} · {total} tugas tersedia</span>
            <div class="pagination-buttons">
              <button class="btn-page" onclick={() => goToPage(page - 1)} disabled={page <= 1}>←</button>
              {#each pages as p}
                {#if p === '...'}
                  <span class="btn-page dots">…</span>
                {:else}
                  <button class="btn-page {page === p ? 'active-page' : ''}" onclick={() => goToPage(p)}>{p}</button>
                {/if}
              {/each}
              <button class="btn-page" onclick={() => goToPage(page + 1)} disabled={page >= lastPage}>→</button>
            </div>
          </div>
        {/if}
      {/if}
    </div>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="shell footer-row">
      <div class="footer-brand-group">
        <div class="footer-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="footer-logo-img" />
        </div>
        <div>
          <span class="footer-brand">KERJAIN</span>
          <p class="footer-sub">Platform kerja mikro untuk Indonesia</p>
        </div>
      </div>
      <p class="copyright">© 2026. Dibuat untuk UMKM dan talenta lokal</p>
    </div>
  </footer>
</div>

<style>
  /* ================= CSS VARIABLES & THEMING ================= */
  :global(:root) {
    --bg-body: #f8fafc;
    --text-primary: #0f172a;
    --text-secondary: #475569;
    --text-muted: #64748b;

    --bg-surface: #ffffff;
    --border-color: #e2e8f0;

    --btn-primary-bg: #15803d;
    --btn-primary-text: #ffffff;
    --btn-primary-hover: #166534;

    --btn-action-bg: #0d233a;
    --btn-action-text: #ffffff;
    --btn-action-hover: #1e293b;

    --hero-bg: #0d233a;
    --hero-text: #ffffff;
    --hero-subtitle: #94a3b8;
    --hero-divider: rgba(255, 255, 255, 0.15);
    
    --search-icon: #8b96a5;

    --tab-bg: #ffffff;
    --tab-text: #475569;
    --tab-active-bg: #0d233a;
    --tab-active-text: #ffffff;

    --badge-cat-bg: #dcfce7;
    --badge-cat-text: #166534;
    --tag-bg: #f1f5f9;
    --tag-text: #64748b;

    --footer-bg: #0d233a;
    --footer-border: #1e293b;
    
    --brand-text: #0d233a;
  }

  /* Dark Mode Overrides */
  :global(body.dark-theme) {
    --bg-body: #0f172a;
    --text-primary: #f8fafc;
    --text-secondary: #cbd5e1;
    --text-muted: #94a3b8;

    --bg-surface: #1e293b;
    --border-color: #334155;

    --btn-primary-bg: #10b981;
    --btn-primary-text: #0f172a;
    --btn-primary-hover: #059669;

    --btn-action-bg: #d9f99d;
    --btn-action-text: #0f172a;
    --btn-action-hover: #bef264;

    --hero-bg: #09131f;
    --hero-text: #ffffff;
    --hero-subtitle: #94a3b8;
    --hero-divider: rgba(255, 255, 255, 0.1);
    
    --search-icon: #94a3b8;

    --tab-bg: #1e293b;
    --tab-text: #cbd5e1;
    --tab-active-bg: #d9f99d;
    --tab-active-text: #0f172a;

    --badge-cat-bg: rgba(34, 197, 94, 0.2);
    --badge-cat-text: #4ade80;
    --tag-bg: #334155;
    --tag-text: #e2e8f0;

    --footer-bg: #09131f;
    --footer-border: #1e293b;
    
    --brand-text: #f8fafc;
  }

  /* ================= GLOBAL STYLES ================= */
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: var(--bg-body);
    color: var(--text-primary);
    -webkit-font-smoothing: antialiased;
    transition: background-color 0.3s, color 0.3s;
  }

  * {
    box-sizing: border-box;
  }

  .shell {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 24px;
  }

  /* ---------- Header ---------- */
  .site-header {
    background: var(--bg-surface);
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 50;
    transition: background-color 0.3s, border-color 0.3s;
  }

  .header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 72px;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
  }

  .brand-logo-wrap {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #0d233a; /* Logo background remains steady */
    flex-shrink: 0;
  }

  .brand-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .brand-text {
    display: flex;
    flex-direction: column;
  }

  .brand-name {
    font-weight: 800;
    font-size: 18px;
    letter-spacing: 0.02em;
    color: var(--brand-text);
    line-height: 1.1;
    transition: color 0.3s;
  }

  .brand-tagline {
    font-size: 11px;
    color: #10b981;
    font-weight: 500;
  }

  .site-nav {
    display: flex;
    align-items: center;
    gap: 16px; /* Reduced gap slightly to fit button */
  }
  
  @media (min-width: 768px) {
    .site-nav { gap: 32px; }
  }

  .theme-toggle {
    background: var(--tag-bg);
    color: var(--text-primary);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.2s;
  }

  .theme-toggle:hover {
    background: var(--border-color);
  }

  .btn-primary-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background-color: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.2s, color 0.2s;
  }

  .btn-primary-header:hover {
    background-color: var(--btn-primary-hover);
  }

  /* ---------- Hero Section ---------- */
  .hero-section {
    background-color: var(--hero-bg);
    color: var(--hero-text);
    padding: 72px 0 88px;
    position: relative;
    overflow: hidden;
    transition: background-color 0.3s;
  }

  .hero-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
  }

  @media (min-width: 900px) {
    .hero-container {
      grid-template-columns: 1.4fr 0.8fr;
    }
  }

  .hero-title {
    font-size: clamp(2.4rem, 4vw, 3.6rem);
    font-weight: 800;
    line-height: 1.15;
    margin: 0 0 20px;
    letter-spacing: -0.02em;
  }

  .highlight-text {
    color: #d9f99d;
  }

  .hero-subtitle {
    color: var(--hero-subtitle);
    font-size: 16px;
    line-height: 1.6;
    max-width: 520px;
    margin-bottom: 36px;
    transition: color 0.3s;
  }

  .search-box {
    background: var(--bg-surface);
    border-radius: 12px;
    padding: 6px 6px 6px 16px;
    display: flex;
    align-items: center;
    max-width: 560px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    transition: background-color 0.3s;
  }

  .search-icon {
    margin-right: 12px;
    stroke: var(--search-icon);
  }

  .search-box input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: var(--text-primary);
    background: transparent;
  }

  .btn-search {
    background-color: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.2s, color 0.2s;
  }

  .btn-search:hover {
    background-color: var(--btn-primary-hover);
  }

  .hero-stats-panel {
    display: flex;
    align-items: flex-start;
    gap: 32px;
    padding-top: 12px;
  }

  .stats-divider {
    width: 1px;
    height: 180px;
    background-color: var(--hero-divider);
    transition: background-color 0.3s;
  }

  .stat-number {
    font-size: 80px;
    font-weight: 900;
    color: #d9f99d;
    line-height: 0.9;
    letter-spacing: -0.03em;
  }

  .stat-label {
    font-size: 18px;
    font-weight: 700;
    color: var(--hero-text);
    margin: 12px 0 32px;
    line-height: 1.3;
  }

  .quote-box {
    border-top: 1px solid var(--hero-divider);
    padding-top: 16px;
  }

  .quote-text {
    font-style: italic;
    color: var(--hero-subtitle);
    font-size: 13.5px;
    margin: 0 0 12px;
  }

  .quote-footer {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
  }

  .check-icon {
    color: #d9f99d;
    font-weight: bold;
  }

  .check-text {
    color: #d9f99d;
    font-weight: 600;
  }

  /* ---------- Main Content ---------- */
  .main-content {
    padding: 48px 0 80px;
  }

  .section-label {
    color: #10b981;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    margin-bottom: 8px;
  }

  .tasks-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
  }

  .section-title {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
    transition: color 0.3s;
  }

  .task-count {
    color: var(--text-muted);
    font-size: 14px;
    font-weight: 500;
    transition: color 0.3s;
  }

  .category-tabs {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 32px;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .category-dropdown-wrap {
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }

  .category-select {
    background: var(--tab-bg);
    border: 1px solid var(--border-color);
    color: var(--tab-text);
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
  }

  .tab-btn {
    background: var(--tab-bg);
    border: 1px solid var(--border-color);
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: var(--tab-text);
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
  }

  .tab-btn.active {
    background-color: var(--tab-active-bg);
    color: var(--tab-active-text);
    border-color: var(--tab-active-bg);
  }

  /* ---------- Loading / Error / Empty States ---------- */
  .loading-state,
  .error-state,
  .empty-state {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 48px 24px;
    text-align: center;
    color: var(--text-secondary);
    transition: background-color 0.3s, border-color 0.3s, color 0.3s;
  }

  .spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--border-color);
    border-top-color: #15803d;
    border-radius: 50%;
    margin: 0 auto 12px;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  .error-state p {
    margin: 0 0 16px;
  }

  .empty-state p {
    margin: 0;
    font-weight: 600;
  }

  /* ---------- Tasks Grid & Cards ---------- */
  .tasks-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
  }

  @media (min-width: 768px) {
    .tasks-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  .task-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s, box-shadow 0.2s, background-color 0.3s, border-color 0.3s;
  }

  .task-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
  }

  .badge-cat {
    background: var(--badge-cat-bg);
    color: var(--badge-cat-text);
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    transition: background-color 0.3s, color 0.3s;
  }

  .status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #10b981;
  }

  .status-indicator .dot {
    width: 7px;
    height: 7px;
    background-color: #22c55e;
    border-radius: 50%;
  }

  .task-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 12px;
    line-height: 1.3;
    transition: color 0.3s;
  }

  .card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 14px;
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--text-muted);
    transition: color 0.3s;
  }

  .task-desc {
    font-size: 14px;
    color: var(--text-secondary);
    line-height: 1.5;
    margin: 0 0 16px;
    transition: color 0.3s;
  }

  .tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 24px;
  }

  .tag-pill {
    background-color: var(--tag-bg);
    color: var(--tag-text);
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    transition: background-color 0.3s, color 0.3s;
  }

  .card-footer {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    transition: border-color 0.3s;
  }

  .reward-label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: var(--text-muted);
    letter-spacing: 0.05em;
    margin-bottom: 2px;
    transition: color 0.3s;
  }

  .reward-value {
    font-size: 20px;
    font-weight: 800;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s;
  }

  .duration-label {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 4px;
    transition: color 0.3s;
  }

  .btn-action {
    background-color: var(--btn-action-bg);
    color: var(--btn-action-text);
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: background-color 0.2s, color 0.2s;
  }

  .btn-action:hover {
    background-color: var(--btn-action-hover);
  }

  /* ---------- Pagination ---------- */
  .pagination-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    margin-top: 28px;
    padding-top: 16px;
  }

  @media (min-width: 640px) {
    .pagination-container {
      flex-direction: row;
      justify-content: space-between;
    }
  }

  .pagination-info {
    font-size: 12px;
    color: var(--text-muted);
    transition: color 0.3s;
  }

  .pagination-buttons {
    display: flex;
    gap: 4px;
    align-items: center;
  }

  .btn-page {
    background: var(--tab-bg);
    border: 1px solid var(--border-color);
    color: var(--tab-text);
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-page:hover:not(:disabled) {
    background: var(--tag-bg);
    color: var(--text-primary);
  }

  .btn-page.active-page {
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-color: var(--btn-primary-bg);
  }

  .btn-page:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .btn-page.dots {
    background: transparent;
    border-color: transparent;
    cursor: default;
    color: var(--text-muted);
  }

  /* ---------- Footer ---------- */
  .site-footer {
    background-color: var(--footer-bg);
    border-top: 1px solid var(--footer-border);
    padding: 32px 0;
    color: #94a3b8;
    transition: background-color 0.3s, border-color 0.3s;
  }

  .footer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .footer-brand-group {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .footer-logo-wrap {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #0d233a;
    flex-shrink: 0;
  }

  .footer-logo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .footer-brand {
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0.05em;
    font-size: 15px;
  }

  .footer-sub {
    margin: 2px 0 0;
    font-size: 12px;
    color: #94a3b8;
  }

  .copyright {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
  }
</style>