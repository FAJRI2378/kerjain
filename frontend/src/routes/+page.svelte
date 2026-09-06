<script>
  let searchQuery = "";
  let activeTab = "Semua";

  const categories = ["Semua", "Konten & Media", "Desain", "Administrasi", "Operasional"];

  const tasks = [
    {
      id: 1,
      category: "Konten & Media",
      status: "Buka",
      title: "Foto Produk 20 Menu + Upload ke Gofood",
      employer: "Soto Ayam Pak Budi",
      location: "1.4 km · Tebet, Jakarta Selatan",
      description: "Dibutuhkan anak muda atau mahasiswa yang paham mengambil foto makanan aesthetic menggunakan HP.",
      tags: ["#Foto", "#Content Creator", "#Entry Data"],
      reward: "Rp 75.000",
      duration: "3 Jam"
    },
    {
      id: 2,
      category: "Desain",
      status: "Buka",
      title: "Desain Poster Promo Grand Opening (A3)",
      employer: "Kopi Kenangan Lokal",
      location: "0.8 km · Pancasila, Depok",
      description: "Buat poster promo beli 1 gratis 1 yang menarik untuk dipasang di depan kedai kopi.",
      tags: ["#Canva", "#Desain Grafis"],
      reward: "Rp 50.000",
      duration: "2 Jam"
    },
    {
      id: 3,
      category: "Administrasi",
      status: "Buka",
      title: "Bantu Input Stok Barang ke Excel/Aplikasi",
      employer: "Toko Berkah Kelontong",
      location: "2.1 km · Margonda, Depok",
      description: "Rapikan stok sembako yang baru datang dan catat ke dalam file Excel toko.",
      tags: ["#Excel", "#Input Data", "#Teliti"],
      reward: "Rp 100.000",
      duration: "4 Jam"
    },
    {
      id: 4,
      category: "Operasional",
      status: "Buka",
      title: "Bantu Packing 100 Box Snack Box",
      employer: "Dapur Mama Snack",
      location: "3.0 km · Kukusan, Depok",
      description: "Bantu packing snack box acara seminar untuk esok pagi.",
      tags: ["#Physical Work", "#Bantu Event"],
      reward: "Rp 60.000",
      duration: "2.5 Jam"
    }
  ];

  let filteredTasks = $derived(
    activeTab === "Semua"
      ? tasks
      : tasks.filter((task) => task.category === activeTab)
  );
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

        <div class="search-box">
          <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b96a5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input 
            type="text" 
            placeholder="Cari Foto, Excel, Desain..." 
            bind:value={searchQuery}
          />
          <button class="btn-search">Cari Tugas</button>
        </div>
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
        <span class="task-count">{filteredTasks.length} tugas tersedia</span>
      </div>

      <!-- Category Filter Tabs -->
      <div class="category-tabs">
        {#each categories as cat}
          <button 
            class="tab-btn {activeTab === cat ? 'active' : ''}" 
            on:click={() => activeTab = cat}
          >
            {cat}
          </button>
        {/each}
      </div>

      <!-- Task Cards Grid -->
      <div class="tasks-grid">
        {#each filteredTasks as task (task.id)}
          <div class="task-card">
            <div class="card-header">
              <span class="badge-cat">{task.category}</span>
              <span class="status-indicator">
                <span class="dot"></span> {task.status}
              </span>
            </div>

            <h3 class="task-title">{task.title}</h3>

            <div class="card-meta">
              <span class="meta-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                {task.employer}
              </span>
              <span class="meta-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                {task.location}
              </span>
            </div>

            <p class="task-desc">{task.description}</p>

            <div class="tags-container">
              {#each task.tags as tag}
                <span class="tag-pill">{tag}</span>
              {/each}
            </div>

            <div class="card-footer">
              <div class="reward-info">
                <span class="reward-label">IMBALAN</span>
                <div class="reward-value">
                  {task.reward} 
                  <span class="duration-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {task.duration}
                  </span>
                </div>
              </div>

              <button class="btn-action">
                Ambil Tugas <span class="arrow">→</span>
              </button>
            </div>
          </div>
        {/each}
      </div>
    </div>
  </main>

  <!-- Footer -->
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

  .shell {
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 24px;
  }

  /* ---------- Footer Styles ---------- */
  .site-footer {
    background-color: #0d233a;
    border-top: 1px solid #1e293b;
    padding: 36px 0;
    color: #94a3b8;
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
    background-color: #1e293b;
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

  /* ---------- Header ---------- */
  .site-header {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 50;
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
    background-color: #0d233a;
    flex-shrink: 0;
  }

  .brand-img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Agar logo tidak terpotong */
  }

  .brand-text {
    display: flex;
    flex-direction: column;
  }

  .brand-name {
    font-weight: 800;
    font-size: 18px;
    letter-spacing: 0.02em;
    color: #0d233a;
    line-height: 1.1;
  }

  .brand-tagline {
    font-size: 11px;
    color: #10b981;
    font-weight: 500;
  }

  .site-nav {
    display: flex;
    align-items: center;
    gap: 32px;
  }

  .btn-primary-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background-color: #15803d;
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    transition: background-color 0.2s;
  }

  .btn-primary-header:hover {
    background-color: #166534;
  }

  /* ---------- Hero Section ---------- */
  .hero-section {
    background-color: #0d233a;
    color: #ffffff;
    padding: 72px 0 88px;
    position: relative;
    overflow: hidden;
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
    color: #94a3b8;
    font-size: 16px;
    line-height: 1.6;
    max-width: 520px;
    margin-bottom: 36px;
  }

  .search-box {
    background: #ffffff;
    border-radius: 12px;
    padding: 6px 6px 6px 16px;
    display: flex;
    align-items: center;
    max-width: 560px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  }

  .search-icon {
    margin-right: 12px;
  }

  .search-box input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: #1e293b;
  }

  .btn-search {
    background-color: #15803d;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 0.2s;
  }

  .btn-search:hover {
    background-color: #166534;
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
    background-color: rgba(255, 255, 255, 0.15);
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
    color: #ffffff;
    margin: 12px 0 32px;
    line-height: 1.3;
  }

  .quote-box {
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    padding-top: 16px;
  }

  .quote-text {
    font-style: italic;
    color: #94a3b8;
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
    color: #15803d;
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
    color: #0d233a;
    margin: 0;
  }

  .task-count {
    color: #94a3b8;
    font-size: 14px;
    font-weight: 500;
  }

  .category-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 32px;
    overflow-x: auto;
    padding-bottom: 4px;
  }

  .tab-btn {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 700;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
  }

  .tab-btn.active {
    background-color: #0d233a;
    color: #ffffff;
    border-color: #0d233a;
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
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: transform 0.2s, box-shadow 0.2s;
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
    background: #dcfce7;
    color: #166534;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
  }

  .status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #166534;
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
    color: #0f172a;
    margin: 0 0 12px;
    line-height: 1.3;
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
    color: #64748b;
  }

  .task-desc {
    font-size: 14px;
    color: #475569;
    line-height: 1.5;
    margin: 0 0 16px;
  }

  .tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 24px;
  }

  .tag-pill {
    background-color: #f1f5f9;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
  }

  .card-footer {
    margin-top: auto;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }

  .reward-label {
    display: block;
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.05em;
    margin-bottom: 2px;
  }

  .reward-value {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .duration-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .btn-action {
    background-color: #0d233a;
    color: #ffffff;
    border: none;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background-color 0.2s;
  }

  .btn-action:hover {
    background-color: #1e293b;
  }

  /* ---------- Footer ---------- */
  .site-footer {
    background-color: #0d233a;
    border-top: 1px solid #1e293b;
    padding: 32px 0;
    color: #94a3b8;
  }

  .footer-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
  }

  .footer-brand {
    font-weight: 800;
    color: #ffffff;
    letter-spacing: 0.05em;
  }

  .footer-sub {
    margin: 2px 0 0;
    font-size: 12px;
  }

  .copyright {
    margin: 0;
  }
</style>