<script>
  // Siap dihubungkan ke state/route SvelteKit

  const categories = [
    { title: "Layanan AI", icon: "🤖", accent: "#34d399", count: "120", tag: "Baru" },
    { title: "Pengembangan & IT", icon: "💻", accent: "#60a5fa", count: "450", tag: null },
    { title: "Desain & Kreatif", icon: "🎨", accent: "#c084fc", count: "310", tag: null },
    { title: "Penjualan & Pemasaran", icon: "📈", accent: "#fbbf24", count: "280", tag: null },
    { title: "Penulisan & Penerjemahan", icon: "✍️", accent: "#fb7185", count: "190", tag: null },
    { title: "Admin & Dukungan", icon: "🎧", accent: "#2dd4bf", count: "210", tag: null },
    { title: "Keuangan & Akuntansi", icon: "📊", accent: "#818cf8", count: "95", tag: null },
    { title: "Legal & Hukum", icon: "⚖️", accent: "#22d3ee", count: "60", tag: null }
  ];

  const stats = [
    { value: "12rb+", label: "Tugas selesai bulan ini" },
    { value: "94%", label: "Tingkat kecocokan tugas" },
    { value: "< 2 jam", label: "Rata-rata waktu respons" }
  ];

  const roles = [
    {
      href: "/login",
      icon: "🛠️",
      accent: "#34d399",
      label: "Untuk pencari kerja",
      title: "Freelancer",
      desc: "Cari tugas mikro di sekitar tempat tinggalmu dan hasilkan pendapatan harian secara fleksibel.",
      cta: "Mulai cari tugas"
    },
    {
      href: "/login",
      icon: "🏢",
      accent: "#60a5fa",
      label: "Untuk pemilik usaha",
      title: "UMKM / Employer",
      desc: "Buka lowongan tugas singkat & temukan bantuan pekerja lokal berbakat dengan proses mudah.",
      cta: "Pasang pekerjaan"
    },
  ];
</script>

<div class="page">

  <div class="noise" aria-hidden="true"></div>
  <div class="grid-field" aria-hidden="true"></div>
  <div class="glow glow-a" aria-hidden="true"></div>
  <div class="glow glow-b" aria-hidden="true"></div>

  <header class="site-header">
    <div class="shell header-row">
      <a href="/" class="brand">
        <span class="brand-mark">K</span>
        <span class="brand-word">Kerjain<span class="brand-dot">.</span></span>
      </a>

      <nav class="site-nav">
        <a href="#kategori">Kategori</a>
        <a href="#peran">Pilih peran</a>
      </nav>

      <div class="header-actions">
        <a href="/login" class="btn-ghost">Masuk</a>
        <a href="/register" class="btn-solid">Daftar sekarang</a>
      </div>
    </div>
  </header>

  <main class="shell">

    <section class="hero">
      <div class="hero-copy">
        
        <h1 class="hero-title">
          Kerja kecil, hasil nyata,
          <span class="hero-title-accent">tumbuh bersama tetanggamu</span>
        </h1>

        <p class="hero-desc">
          Kerjain menghubungkan UMKM dengan talenta lokal untuk tugas-tugas
          harian — cepat dikerjakan, cepat dibayar, dan sepenuhnya transparan.
        </p>

        <div class="hero-actions">
          <a href="/register" class="btn-solid btn-lg">Gabung sebagai freelancer</a>
          <a href="/register" class="btn-outline btn-lg">Saya UMKM, mau pasang tugas</a>
        </div>

        <dl class="hero-stats">
          {#each stats as s}
            <div class="hero-stat">
              <dt>{s.value}</dt>
              <dd>{s.label}</dd>
            </div>
          {/each}
        </dl>
      </div>

      <div class="hero-panel" aria-hidden="true">
        <div class="panel-window">
          <div class="panel-topbar">
            <span></span><span></span><span></span>
          </div>
          <div class="panel-body">
            <div class="task-card">
              <div class="task-card-head">
                <span class="task-avatar">RW</span>
                <div>
                  <p class="task-name">Warung Bu Rina</p>
                  <p class="task-meta">Jakarta Selatan · 800m</p>
                </div>
                <span class="task-price">Rp75rb</span>
              </div>
              <p class="task-title">Foto produk untuk katalog online</p>
              <div class="task-tags">
                <span>Fotografi</span>
                <span>2 jam</span>
              </div>
            </div>

            <div class="task-card task-card-dim">
              <div class="task-card-head">
                <span class="task-avatar task-avatar-b">DT</span>
                <div>
                  <p class="task-name">Distro Tigapuluh</p>
                  <p class="task-meta">Bandung · Remote</p>
                </div>
                <span class="task-price">Rp150rb</span>
              </div>
              <p class="task-title">Balas 40 pesan WhatsApp pelanggan</p>
              <div class="task-tags">
                <span>Admin</span>
                <span>Hari ini</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="peran" class="roles">
      <div class="section-head">
        <h2>Masuk sesuai perananmu</h2>
        <p>Tiga pintu, satu platform — pilih yang sesuai dengan kebutuhanmu.</p>
      </div>

      <div class="roles-grid">
        {#each roles as r}
          <a
            href={r.href}
            class="role-card"
            style="--accent: {r.accent}"
          >
            <div class="role-icon">{r.icon}</div>
            <span class="role-label">{r.label}</span>
            <h3 class="role-title">{r.title}</h3>
            <p class="role-desc">{r.desc}</p>
            <span class="role-cta">{r.cta} <span class="role-arrow">→</span></span>
          </a>
        {/each}
      </div>
    </section>

    <section id="kategori" class="categories">
      <div class="section-head section-head-row">
        <div>
          <h2>Eksplorasi kategori pekerjaan</h2>
          <p>Temukan keahlian yang paling sesuai dengan kebutuhan proyekmu.</p>
        </div>
        <a href="/categories" class="link-arrow">Lihat semua kategori →</a>
      </div>

      <div class="categories-grid">
        {#each categories as item}
          <button type="button" class="category-card" style="--accent: {item.accent}">
            {#if item.tag}
              <span class="category-tag">{item.tag}</span>
            {/if}
            <span class="category-icon">{item.icon}</span>
            <span class="category-title">{item.title}</span>
            <span class="category-count">{item.count}+ pekerjaan aktif</span>
          </button>
        {/each}
      </div>
    </section>

    <section id="tentang" class="cta-band">
      <div class="cta-band-inner">
        <div>
          <h2>Siap kerjain tugas pertamamu?</h2>
          <p>Daftar gratis dalam 2 menit. Tanpa biaya tersembunyi, tanpa komitmen jangka panjang.</p>
        </div>
        <a href="/register" class="btn-solid btn-lg">Daftar sekarang</a>
      </div>
    </section>

  </main>

  <footer class="site-footer">
    <div class="shell footer-row">
      <p>© 2026 <strong>Kerjain</strong>. Dibuat khusus untuk UMKM &amp; talenta lokal Indonesia.</p>
      <nav class="footer-nav">
        <a href="/privacy">Kebijakan privasi</a>
        <a href="/terms">Syarat &amp; ketentuan</a>
        <a href="/help">Pusat bantuan</a>
      </nav>
    </div>
  </footer>

</div>

<style>
  :global(html) {
    color-scheme: dark;
  }

  * {
    box-sizing: border-box;
  }

  .page {
    position: relative;
    min-height: 100vh;
    background: #08090c;
    color: #e7e9ee;
    font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
    overflow-x: hidden;
    isolation: isolate;
  }

  .shell {
    max-width: 1180px;
    margin: 0 auto;
    padding-left: 24px;
    padding-right: 24px;
  }

  /* ---------- background texture ---------- */

  .noise {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0.035;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  }

  .grid-field {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background-image:
      linear-gradient(to right, rgba(255,255,255,0.035) 1px, transparent 1px),
      linear-gradient(to bottom, rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size: 56px 56px;
    mask-image: radial-gradient(ellipse 70% 55% at 50% 0%, black 10%, transparent 75%);
  }

  .glow {
    position: fixed;
    z-index: 0;
    pointer-events: none;
    border-radius: 50%;
    filter: blur(120px);
  }
  .glow-a {
    top: -220px;
    left: 50%;
    transform: translateX(-50%);
    width: 900px;
    height: 480px;
    background: radial-gradient(closest-side, rgba(52,211,153,0.16), transparent 70%);
  }
  .glow-b {
    top: 40%;
    right: -220px;
    width: 560px;
    height: 480px;
    background: radial-gradient(closest-side, rgba(96,165,250,0.10), transparent 70%);
  }

  /* ---------- header ---------- */

  .site-header {
    position: sticky;
    top: 0;
    z-index: 40;
    backdrop-filter: blur(14px);
    background: rgba(8,9,12,0.72);
    border-bottom: 1px solid rgba(255,255,255,0.07);
  }

  .header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 68px;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: inherit;
  }

  .brand-mark {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    font-size: 15px;
    color: #06251d;
    background: linear-gradient(135deg, #34d399, #2dd4bf);
    box-shadow: 0 6px 18px rgba(52,211,153,0.28);
  }

  .brand-word {
    font-weight: 800;
    font-size: 18px;
    letter-spacing: -0.02em;
    color: #fff;
  }
  .brand-dot { color: #34d399; }

  .site-nav {
    display: none;
    align-items: center;
    gap: 30px;
    font-size: 14px;
    font-weight: 500;
    color: #a8adb8;
  }
  .site-nav a { color: inherit; text-decoration: none; transition: color 0.15s ease; }
  .site-nav a:hover { color: #34d399; }

  @media (min-width: 860px) {
    .site-nav { display: flex; }
  }

  .header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* ---------- buttons ---------- */

  .btn-ghost, .btn-solid, .btn-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    text-decoration: none;
    border-radius: 11px;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease;
    white-space: nowrap;
  }

  .btn-ghost {
    padding: 9px 14px;
    font-size: 13px;
    color: #c7cbd4;
  }
  .btn-ghost:hover { color: #fff; }

  .btn-solid {
    padding: 10px 18px;
    font-size: 13px;
    color: #06251d;
    background: #34d399;
    box-shadow: 0 6px 16px rgba(52,211,153,0.22);
  }
  .btn-solid:hover {
    background: #4ee0ab;
    box-shadow: 0 8px 22px rgba(52,211,153,0.32);
    transform: translateY(-1px);
  }

  .btn-outline {
    padding: 10px 18px;
    font-size: 13px;
    color: #e7e9ee;
    border: 1px solid rgba(255,255,255,0.16);
    background: rgba(255,255,255,0.02);
  }
  .btn-outline:hover {
    border-color: rgba(255,255,255,0.32);
    background: rgba(255,255,255,0.05);
  }

  .btn-lg {
    padding: 13px 22px;
    font-size: 14.5px;
    border-radius: 12px;
  }

  /* ---------- hero ---------- */

  .hero {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: 1fr;
    gap: 48px;
    padding-top: 72px;
    padding-bottom: 88px;
    align-items: center;
  }

  @media (min-width: 980px) {
    .hero {
      grid-template-columns: 1.05fr 0.95fr;
      padding-top: 96px;
      padding-bottom: 120px;
    }
  }

  .pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 14px;
    border-radius: 999px;
    background: rgba(52,211,153,0.09);
    border: 1px solid rgba(52,211,153,0.28);
    color: #6ee7b7;
    font-size: 12.5px;
    font-weight: 600;
    margin-bottom: 22px;
  }
  .pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52,211,153,0.18);
  }

  .hero-title {
    font-size: clamp(2.1rem, 4.6vw, 3.4rem);
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -0.025em;
    color: #fff;
    margin: 0 0 20px;
  }

  .hero-title-accent {
    display: block;
    background: linear-gradient(100deg, #6ee7b7, #5eead4 45%, #67e8f9);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }

  .hero-desc {
    font-size: 16.5px;
    line-height: 1.65;
    color: #9ba1ad;
    max-width: 46ch;
    margin: 0 0 32px;
  }

  .hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 44px;
  }

  .hero-stats {
    display: grid;
    grid-template-columns: repeat(3, auto);
    gap: 28px;
    margin: 0;
    padding-top: 28px;
    border-top: 1px solid rgba(255,255,255,0.08);
    max-width: 460px;
  }
  .hero-stat dt {
    font-size: 21px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 2px;
  }
  .hero-stat dd {
    font-size: 12px;
    color: #7d8390;
    margin: 0;
    line-height: 1.4;
  }

  /* ---------- hero visual panel ---------- */

  .hero-panel {
    position: relative;
  }

  .panel-window {
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.09);
    background: linear-gradient(165deg, rgba(255,255,255,0.045), rgba(255,255,255,0.015));
    box-shadow: 0 30px 70px -20px rgba(0,0,0,0.6);
    overflow: hidden;
    backdrop-filter: blur(6px);
  }

  .panel-topbar {
    display: flex;
    gap: 6px;
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .panel-topbar span {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: rgba(255,255,255,0.14);
  }

  .panel-body {
    padding: 22px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .task-card {
    border-radius: 14px;
    padding: 16px;
    background: rgba(255,255,255,0.035);
    border: 1px solid rgba(255,255,255,0.08);
  }
  .task-card-dim { opacity: 0.62; }

  .task-card-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
  }

  .task-avatar {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #06251d;
    background: #34d399;
  }
  .task-avatar-b { background: #60a5fa; color: #061a33; }

  .task-name {
    font-size: 13px;
    font-weight: 600;
    color: #eef0f3;
    margin: 0;
  }
  .task-meta {
    font-size: 11.5px;
    color: #7d8390;
    margin: 1px 0 0;
  }
  .task-price {
    margin-left: auto;
    font-size: 13px;
    font-weight: 700;
    color: #6ee7b7;
    white-space: nowrap;
  }

  .task-title {
    font-size: 14px;
    font-weight: 500;
    color: #d5d8de;
    margin: 0 0 12px;
    line-height: 1.4;
  }

  .task-tags {
    display: flex;
    gap: 8px;
  }
  .task-tags span {
    font-size: 10.5px;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 6px;
    background: rgba(255,255,255,0.05);
    color: #9ba1ad;
    border: 1px solid rgba(255,255,255,0.06);
  }

  /* ---------- section head ---------- */

  .section-head {
    margin-bottom: 32px;
  }
  .section-head h2 {
    font-size: clamp(1.5rem, 2.6vw, 1.9rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #fff;
    margin: 0 0 8px;
  }
  .section-head p {
    font-size: 14.5px;
    color: #8b909c;
    margin: 0;
  }

  .section-head-row {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  @media (min-width: 700px) {
    .section-head-row {
      flex-direction: row;
      justify-content: space-between;
      align-items: flex-end;
    }
  }

  .link-arrow {
    font-size: 13px;
    font-weight: 600;
    color: #34d399;
    text-decoration: none;
    white-space: nowrap;
  }
  .link-arrow:hover { color: #6ee7b7; }

  /* ---------- roles ---------- */

  .roles {
    position: center;
    z-index: 1;
    padding-bottom: 150px;
  }

  .roles-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
  @media (min-width: 780px) {
    .roles-grid { flex-direction: row; }
  }

  .role-card {
    position: relative;
    display: flex;
    flex-direction: column;
    padding: 26px 24px;
    border-radius: 18px;
    text-decoration: none;
    color: inherit;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
  }
  .role-card:hover {
    border-color: color-mix(in srgb, var(--accent) 45%, transparent);
    background: rgba(255,255,255,0.045);
    transform: translateY(-3px);
  }

  .role-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    margin-bottom: 18px;
    background: color-mix(in srgb, var(--accent) 14%, transparent);
    border: 1px solid color-mix(in srgb, var(--accent) 30%, transparent);
  }

  .role-label {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--accent);
    margin-bottom: 6px;
  }

  .role-title {
    font-size: 18px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 10px;
    letter-spacing: -0.01em;
  }

  .role-desc {
    font-size: 13.5px;
    line-height: 1.55;
    color: #8b909c;
    margin: 0 0 20px;
    flex-grow: 1;
  }

  .role-cta {
    font-size: 13px;
    font-weight: 600;
    color: #dfe1e6;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .role-arrow {
    transition: transform 0.2s ease;
    color: var(--accent);
  }
  .role-card:hover .role-arrow { transform: translateX(3px); }

  /* ---------- categories ---------- */

  .categories {
    position: relative;
    z-index: 1;
    padding-bottom: 100px;
  }

  .categories-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  @media (min-width: 700px) {
    .categories-grid { grid-template-columns: repeat(4, 1fr); }
  }

  .category-card {
    position: relative;
    text-align: left;
    padding: 20px 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.025);
    border: 1px solid rgba(255,255,255,0.07);
    cursor: pointer;
    font: inherit;
    color: inherit;
    transition: border-color 0.2s ease, background 0.2s ease, transform 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  .category-card:hover {
    border-color: color-mix(in srgb, var(--accent) 42%, transparent);
    background: rgba(255,255,255,0.045);
    transform: translateY(-2px);
  }

  .category-tag {
    position: absolute;
    top: 14px;
    right: 14px;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 999px;
    color: #06251d;
    background: #34d399;
  }

  .category-icon {
    font-size: 24px;
    line-height: 1;
  }

  .category-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #eef0f3;
    line-height: 1.3;
  }

  .category-count {
    font-size: 12px;
    font-weight: 500;
    color: #757b87;
  }

  /* ---------- cta band ---------- */

  .cta-band {
    position: relative;
    z-index: 1;
    padding-bottom: 96px;
  }

  .cta-band-inner {
    border-radius: 22px;
    padding: 44px 36px;
    background: linear-gradient(135deg, rgba(52,211,153,0.11), rgba(45,212,191,0.05));
    border: 1px solid rgba(52,211,153,0.22);
    display: flex;
    flex-direction: column;
    gap: 24px;
    align-items: flex-start;
  }
  @media (min-width: 780px) {
    .cta-band-inner {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .cta-band-inner h2 {
    font-size: clamp(1.4rem, 2.4vw, 1.7rem);
    font-weight: 800;
    color: #fff;
    margin: 0 0 8px;
    letter-spacing: -0.02em;
  }
  .cta-band-inner p {
    font-size: 14px;
    color: #a8ac9f;
    margin: 0;
    max-width: 44ch;
  }

  /* ---------- footer ---------- */

  .site-footer {
    position: relative;
    z-index: 1;
    border-top: 1px solid rgba(255,255,255,0.07);
  }

  .footer-row {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 30px 24px;
    font-size: 12.5px;
    color: #6d7280;
  }
  @media (min-width: 700px) {
    .footer-row {
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
    }
  }
  .footer-row strong { color: #a8adb8; }

  .footer-nav {
    display: flex;
    gap: 22px;
  }
  .footer-nav a {
    color: inherit;
    text-decoration: none;
  }
  .footer-nav a:hover { color: #c7cbd4; }
</style>