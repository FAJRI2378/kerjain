<script>
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { initials } from '$lib/format.js';

  let { children } = $props();

  const navItems = [
    { name: 'Dashboard', path: '/hire/dashboard', icon: '📊' },
    { name: 'Buat Tugas Baru', path: '/hire/jobs/create', icon: '➕' },
    { name: 'Kelola Jobs', path: '/hire/jobs', icon: '📁' },
    { name: 'Invoice & Kuitansi', path: '/hire/invoices', icon: '🧾' },
    { name: 'Kontak Freelancer', path: '/hire/kontak', icon: '📞' },
    { name: 'Reviews', path: '/hire/reviews', icon: '📜' },
    { name: 'Dompet Escrow', path: '/hire/wallet', icon: '💰' },
    { name: 'Verifikasi Usaha', path: '/hire/verifikasi', icon: '🏪' },
    { name: 'Profil', path: '/hire/profile', icon: '👤' }
  ];

  let isMobileMenuOpen = $state(false);

  let isDarkMode = $state(false);

  function getUserAvatar(userObj) {
    if (!userObj) return null;
    return userObj.avatar_url || userObj.avatar || userObj.profile_photo_url || userObj.photo_url || userObj.photo || null;
  }

  onMount(() => {
    if (!auth.hydrated) auth.hydrate();

    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark') {
        isDarkMode = true;
      }
    }
  });

  $effect(() => {
    if (typeof document !== 'undefined') {
      if (isDarkMode) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark');
      } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('theme', 'light');
      }
    }
  });

  $effect(() => {
    if (auth.hydrated) {
      if (!auth.user) goto('/login', { replaceState: true });
      else if (auth.user.role !== 'hirer') goto('/', { replaceState: true });
    }
  });

  async function handleLogout() {
    await auth.logout();
    goto('/');
  }
</script>

<div class="layout-container">
  <!-- Mobile Top Bar -->
  <header class="mobile-header">
    <a href="/hire/dashboard" class="brand">
      <div class="brand-logo-wrap">
        <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
      </div>
      <div class="brand-text">
        <span class="brand-name">KERJAIN</span>
        <span class="brand-tag">HIRER</span>
      </div>
    </a>
    <button 
      onclick={() => isMobileMenuOpen = !isMobileMenuOpen}
      class="btn-toggle-menu"
    >
      {isMobileMenuOpen ? '✕ Tutup' : '☰ Menu'}
    </button>
  </header>

  <!-- Sidebar Navigasi -->
  <aside class="sidebar {isMobileMenuOpen ? 'open' : ''}">
    <div class="sidebar-top">
      <a href="/hire/dashboard" class="brand brand-desktop">
        <div class="brand-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
        </div>
        <div class="brand-text">
          <span class="brand-name">KERJAIN<span class="dot">.</span></span>
          <span class="brand-sub">BUSINESS & HIRER</span>
        </div>
      </a>

      <nav class="nav-menu">
        <p class="nav-section-label">Pemberi Kerja</p>
        {#each navItems as item}
          {@const isActive = $page.url.pathname === item.path}
          <a 
            href={item.path}
            onclick={() => isMobileMenuOpen = false}
            class="nav-item {isActive ? 'active' : ''}"
          >
            <span class="nav-icon">{item.icon}</span>
            <span class="nav-label">{item.name}</span>
          </a>
        {/each}
      </nav>
    </div>

    <div class="sidebar-bottom">

      <button 
        class="btn-theme-toggle" 
        onclick={() => isDarkMode = !isDarkMode}
        title="Ubah Tema"
      >
        {isDarkMode ? '🌞 Mode Terang' : '🌙 Mode Gelap'}
      </button>

      <a 
        href="/hire/profile"
        class="user-card-btn"
        title="Buka Profil & Verifikasi Usaha"
      >
        <div class="user-avatar">
          {#if getUserAvatar(auth.user)}
            <img src={getUserAvatar(auth.user)} alt="Logo Usaha" class="avatar-img" />
          {:else}
            {auth.user ? initials(auth.user.name || 'UMKM') : '?'}
          {/if}
        </div>
        <div class="user-info">
          <p class="user-name">{auth.user?.business_profile?.business_name || auth.user?.name || 'UMKM'}</p>
          {#if auth.user?.is_verified}
            <p class="user-status verified">🛡️ Escrow Verified</p>
          {:else}
            <p class="user-status">⚠️ Belum Terverifikasi</p>
          {/if}
        </div>
        <span class="settings-gear">⚙️</span>
      </a>

      <button onclick={handleLogout} class="btn-logout">
        <span>👈</span> Keluar
      </button>
    </div>
  </aside>

  <!-- Main Workspace -->
  <main class="main-workspace">
    {@render children()}
  </main>
</div>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
    transition: background-color 0.3s ease, color 0.3s ease;
  }

  * {
    box-sizing: border-box;
  }

  .layout-container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    transition: background-color 0.3s ease;
  }

  @media (min-width: 768px) {
    .layout-container {
      flex-direction: row;
    }
  }

  :global(body.dark-theme) {
    background-color: #0f172a !important;
    color: #f8fafc !important;
  }

  :global(body.dark-theme .layout-container) {
    background-color: transparent !important;
  }

  :global(body.dark-theme .sidebar),
  :global(body.dark-theme .mobile-header) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }

  :global(body.dark-theme .brand-name),
  :global(body.dark-theme .user-name) {
    color: #ffffff !important;
  }

  :global(body.dark-theme .brand-sub) {
    color: #94a3b8 !important;
  }

  :global(body.dark-theme .nav-item) {
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .nav-item:hover) {
    background-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .nav-item.active) {
    background-color: rgba(21, 128, 61, 0.2) !important;
    color: #4ade80 !important;
  }
  :global(body.dark-theme .nav-section-label) {
    color: #64748b !important;
  }

  :global(body.dark-theme .user-card-btn) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .user-card-btn:hover) {
    background-color: #1e293b !important;
  }

  :global(body.dark-theme .btn-logout) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .btn-logout:hover) {
    background-color: #450a0a !important;
    color: #fca5a5 !important;
    border-color: #7f1d1d !important;
  }

  .mobile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 50;
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  @media (min-width: 768px) {
    .mobile-header {
      display: none;
    }
  }

  .btn-toggle-menu {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
  }

  .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
  }

  .brand-logo-wrap {
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
    font-size: 16px;
    color: #0f172a;
    line-height: 1.1;
  }

  .brand-sub {
    font-size: 9px;
    color: #15803d;
    font-weight: 800;
    letter-spacing: 0.08em;
  }

  .brand-tag {
    font-size: 9px;
    font-weight: 800;
    color: #15803d;
    background: rgba(21, 128, 61, 0.15);
    padding: 2px 6px;
    border-radius: 4px;
  }

  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    background: #ffffff;
    border-right: 1px solid #e2e8f0;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    z-index: 40;
    transform: translateX(-100%);
    transition: transform 0.2s ease-in-out, background-color 0.3s ease, border-color 0.3s ease;
  }

  .sidebar.open {
    transform: translateX(0);
  }

  @media (min-width: 768px) {
    .sidebar {
      position: sticky;
      transform: translateX(0);
      flex-shrink: 0;
    }
  }

  .brand-desktop {
    margin-bottom: 32px;
    padding: 0 8px;
  }

  .nav-section-label {
    font-size: 10px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 0 12px;
    margin-bottom: 8px;
  }

  .nav-menu {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
  }

  .nav-item:hover {
    background: #f1f5f9;
    color: #0f172a;
  }

  .nav-item.active {
    background: rgba(21, 128, 61, 0.12);
    color: #15803d;
    border: 1px solid rgba(21, 128, 61, 0.25);
  }

  .nav-icon {
    font-size: 16px;
  }

  .sidebar-bottom {
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  
  .btn-theme-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 9px;
    background: transparent;
    border: 1px dashed #cbd5e1;
    color: #64748b;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
  }
  .btn-theme-toggle:hover {
    background: #f1f5f9;
    color: #0f172a;
  }
  :global(body.dark-theme .btn-theme-toggle) {
    border-color: #475569;
    color: #94a3b8;
  }
  :global(body.dark-theme .btn-theme-toggle:hover) {
    background: #334155;
    color: #f8fafc;
  }

  .user-card-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    text-align: left;
    width: 100%;
    text-decoration: none;
    transition: background-color 0.2s, border-color 0.2s;
  }

  .user-card-btn:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
  }

  .user-avatar {
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #15803d, #166534);
    color: #ffffff;
    font-weight: 800;
    font-size: 12px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .user-info {
    overflow: hidden;
    flex: 1;
  }

  .user-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .user-status {
    font-size: 10.5px;
    font-weight: 600;
    color: #d97706;
    margin: 1px 0 0;
  }

  .user-status.verified {
    color: #15803d;
  }

  .settings-gear {
    font-size: 14px;
    opacity: 0.6;
    transition: transform 0.2s;
  }

  .user-card-btn:hover .settings-gear {
    transform: rotate(45deg);
    opacity: 1;
  }

  .btn-logout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 9px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b;
    font-size: 12px;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-logout:hover {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
  }

  .main-workspace {
    flex: 1;
    min-width: 0;
  }
</style>