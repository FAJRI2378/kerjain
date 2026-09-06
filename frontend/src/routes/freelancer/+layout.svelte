<script>
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { initials } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let { children } = $props();

  // --- MENU NAVIGASI DIPERBARUI ---
  // Menambahkan menu "Status Lamaran" beserta simulasi notifikasi (badge)
  const navItems = [
    { name: 'Dashboard', path: '/freelancer/dashboard', icon: '📊' },
    { name: 'Cari Jobs', path: '/freelancer/jobs', icon: '🔍' },
    { name: 'Status Lamaran', path: '/freelancer/applications', icon: '📩', badge: 2 }, // Menu Baru
    { name: 'Tugas Saya', path: '/freelancer/mytasks', icon: '📋' },
    { name: 'Chat UMKM', path: '/freelancer/chat', icon: '💬' },
    { name: 'Dompet', path: '/freelancer/wallet', icon: '💰' },
    { name: 'Verifikasi ID', path: '/freelancer/id', icon: '🪪' }
  ];

  let isMobileMenuOpen = $state(false);
  let isProfileOpen = $state(false);
  
  // State untuk Fitur Dark Mode
  let isDarkMode = $state(false);
  
  // Form State Profil
  let profileName = $state('');
  let profileEmail = $state('');
  let profilePhone = $state('');
  let avatarPreview = $state('');
  let avatarFile = $state(null);
  let newPassword = $state('');
  let isSavingProfile = $state(false);

  // Helper untuk membaca URL foto secara aman
  function getUserAvatar(userObj) {
    if (!userObj) return null;
    return userObj.avatar_url || userObj.avatar || userObj.profile_photo_url || userObj.photo_url || userObj.photo || null;
  }

  onMount(() => {
    if (!auth.hydrated) auth.hydrate();
    
    // Cek preferensi tema sebelumnya
    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'dark') {
        isDarkMode = true;
      }
    }
  });

  // Efek reaktif: mengubah kelas body dan menyimpan ke localStorage setiap kali isDarkMode berubah
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
      else if (auth.user.role !== 'freelancer') goto('/', { replaceState: true });
      
      // Sinkronisasi data user ke form profil
      if (auth.user) {
        profileName = auth.user.name || '';
        profileEmail = auth.user.email || '';
        profilePhone = auth.user.phone || '';
        // Set preview awal dengan foto dari server
        if (!avatarFile) {
          avatarPreview = getUserAvatar(auth.user) || '';
        }
      }
    }
  });

  function handleFileChange(e) {
    const file = e.target.files[0];
    if (file) {
      avatarFile = file;
      avatarPreview = URL.createObjectURL(file); // Preview instan dari local file
    }
  }

  async function handleLogout() {
    await auth.logout();
    goto('/');
  }

  async function handleSaveProfile(e) {
    e.preventDefault();
    isSavingProfile = true;

    try {
      const formData = new FormData();
      formData.append('name', profileName);
      formData.append('email', profileEmail);
      formData.append('phone', profilePhone);
      
      if (avatarFile) {
        formData.append('avatar', avatarFile);
        formData.append('photo', avatarFile); 
      }
      
      if (newPassword) {
        formData.append('password', newPassword);
      }

      if (auth.updateProfile) {
        const response = await auth.updateProfile(formData);
        
        if (response && response.user) {
           avatarPreview = getUserAvatar(response.user);
        }
      }
      
      toast('Profil & foto berhasil diperbarui!', 'success');
      isProfileOpen = false;
      newPassword = '';
      avatarFile = null; 
    } catch (err) {
      toast(errorMessage(err, 'Gagal memperbarui profil.'), 'error');
    } finally {
      isSavingProfile = false;
    }
  }
</script>

<div class="layout-container">
  <!-- Mobile Top Bar -->
  <header class="mobile-header">
    <a href="/freelancer/dashboard" class="brand">
      <div class="brand-logo-wrap">
        <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
      </div>
      <div class="brand-text">
        <span class="brand-name">KERJAIN</span>
        <span class="brand-tag">FREELANCER</span>
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
      <!-- Logo Brand -->
      <a href="/freelancer/dashboard" class="brand brand-desktop">
        <div class="brand-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
        </div>
        <div class="brand-text">
          <span class="brand-name">KERJAIN</span>
          <span class="brand-sub">FREELANCER PORTAL</span>
        </div>
      </a>

      <!-- Navigation Links -->
      <nav class="nav-menu">
        <p class="nav-section-label">Workspace</p>
        {#each navItems as item}
          {@const isActive = $page.url.pathname === item.path}
          <a 
            href={item.path}
            onclick={() => isMobileMenuOpen = false}
            class="nav-item {isActive ? 'active' : ''}"
          >
            <div class="nav-content-left">
              <span class="nav-icon">{item.icon}</span>
              <span class="nav-label">{item.name}</span>
            </div>
            
            <!-- BADGE NOTIFIKASI -->
            {#if item.badge}
              <span class="nav-badge animate-pulse">{item.badge}</span>
            {/if}
          </a>
        {/each}
      </nav>
    </div>

    <!-- Bottom Profile & Logout Bar -->
    <div class="sidebar-bottom">
      
      <!-- Tombol Toggle Dark Mode -->
      <button 
        class="btn-theme-toggle" 
        onclick={() => isDarkMode = !isDarkMode}
        title="Ubah Tema"
      >
        {isDarkMode ? '🌞 Mode Terang' : '🌙 Mode Gelap'}
      </button>

      <button 
        type="button" 
        onclick={() => isProfileOpen = true}
        class="user-card-btn"
        title="Buka Pengaturan Profil"
      >
        <div class="user-avatar">
          {#if getUserAvatar(auth.user) || avatarPreview}
            <img src={avatarPreview || getUserAvatar(auth.user)} alt="Foto Profil" class="avatar-img" />
          {:else}
            {auth.user ? initials(auth.user.name) : '?'}
          {/if}
        </div>
        <div class="user-info">
          <p class="user-name">{auth.user?.name ?? 'Freelancer'}</p>
          <p class="user-status {auth.user?.is_verified ? 'verified' : ''}">
            {auth.user?.is_verified ? '✓ Terverifikasi' : '⏳ Belum Verifikasi'}
          </p>
        </div>
        <span class="settings-gear">⚙️</span>
      </button>

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

<!-- Modal Dialog Profil -->
{#if isProfileOpen}
  <div class="modal-backdrop" onclick={() => isProfileOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header">
        <div class="modal-title-group">
          <h2 class="modal-title">Pengaturan Profil</h2>
          <p class="modal-sub">Kelola foto profil, data diri, dan kata sandi Anda.</p>
        </div>
        <button class="btn-close" onclick={() => isProfileOpen = false}>✕</button>
      </div>

      <!-- Section Avatar Upload -->
      <div class="profile-avatar-section">
        <div class="large-avatar">
          {#if avatarPreview}
            <img src={avatarPreview} alt="Foto Profil" class="avatar-img-lg" />
          {:else}
            {auth.user ? initials(auth.user.name) : '?'}
          {/if}
        </div>
        <div class="avatar-upload-info">
          <label for="avatar-input" class="btn-change-avatar">
            📷 Ubah Foto Profil
          </label>
          <input 
            id="avatar-input" 
            type="file" 
            accept="image/png, image/jpeg, image/jpg" 
            onchange={handleFileChange} 
            class="hidden-file-input" 
          />
          <p class="avatar-tip">JPG/PNG, Maks. 2MB</p>
        </div>
      </div>

      <form onsubmit={handleSaveProfile} class="modal-form">
        <div class="form-group">
          <label for="prof-name">Nama Lengkap</label>
          <input 
            id="prof-name" 
            type="text" 
            bind:value={profileName} 
            required 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="prof-email">Alamat Email / Gmail</label>
          <input 
            id="prof-email" 
            type="email" 
            bind:value={profileEmail} 
            required 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="prof-phone">Nomor HP / WhatsApp</label>
          <input 
            id="prof-phone" 
            type="tel" 
            bind:value={profilePhone} 
            placeholder="Contoh: 081234567890" 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="prof-pass">Ubah Kata Sandi <span class="opt">(Opsional)</span></label>
          <input 
            id="prof-pass" 
            type="password" 
            bind:value={newPassword} 
            placeholder="Isi hanya jika ingin merubah" 
            class="form-input" 
          />
        </div>

        <div class="modal-actions">
          <button 
            type="button" 
            class="btn-cancel" 
            onclick={() => { isProfileOpen = false; avatarFile = null; avatarPreview = getUserAvatar(auth.user) || ''; }}
          >
            Batal
          </button>
          <button 
            type="submit" 
            disabled={isSavingProfile} 
            class="btn-save"
          >
            {isSavingProfile ? 'Menyimpan...' : 'Simpan Perubahan'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

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

  /* ---------------- DARK THEME RULES ---------------- */
  :global(body.dark-theme) {
    background-color: #0f172a !important;
    color: #f8fafc !important;
  }

  :global(body.dark-theme .layout-container) {
    background-color: transparent !important;
  }

  :global(body.dark-theme .sidebar), 
  :global(body.dark-theme .mobile-header),
  :global(body.dark-theme .modal-card),
  :global(body.dark-theme .profile-avatar-section) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }

  :global(body.dark-theme .brand-name),
  :global(body.dark-theme .modal-title),
  :global(body.dark-theme .column-title),
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .user-name) {
    color: #ffffff !important;
  }

  :global(body.dark-theme .brand-sub),
  :global(body.dark-theme .modal-sub),
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .opt),
  :global(body.dark-theme .avatar-tip) {
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
  :global(body.dark-theme .form-input) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .form-input:focus) {
    border-color: #22c55e !important;
  }
  :global(body.dark-theme .form-group label) {
    color: #cbd5e1 !important;
  }

  :global(body.dark-theme .btn-cancel),
  :global(body.dark-theme .btn-change-avatar),
  :global(body.dark-theme .btn-toggle-menu) {
    background-color: #334155 !important;
    border-color: #475569 !important;
    color: #f8fafc !important;
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
  
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .card),
  :global(body.dark-theme .task-card),
  :global(body.dark-theme .kanban-column),
  :global(body.dark-theme .empty-state) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .job-item) {
     background-color: #0f172a !important;
     border-color: #334155 !important;
  }
  :global(body.dark-theme .task-title),
  :global(body.dark-theme .greeting),
  :global(body.dark-theme .stat-value) {
    color: #f8fafc !important;
  }
  /* -------------------------------------------------- */

  /* Mobile Header */
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
    letter-spacing: 0.02em;
    color: #0d233a;
    line-height: 1.1;
  }

  .brand-sub {
    font-size: 9px;
    color: #15803d;
    font-weight: 800;
    letter-spacing: 0.05em;
  }

  .brand-tag {
    font-size: 9px;
    font-weight: 800;
    color: #15803d;
    background: #dcfce7;
    padding: 2px 6px;
    border-radius: 4px;
  }

  /* Sidebar */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
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
    color: #94a3b8;
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

  /* Penyesuaian Flexbox untuk Badge */
  .nav-item {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Membuat badge di ujung kanan */
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    text-decoration: none;
    transition: all 0.2s;
  }

  .nav-content-left {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .nav-badge {
    background-color: #ef4444; /* Merah untuk Notifikasi */
    color: white;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 999px;
  }

  /* Animasi pulse untuk notifikasi baru */
  .animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
  }

  .nav-item:hover {
    background: #f1f5f9;
    color: #0d233a;
  }

  .nav-item.active {
    background: #dcfce7;
    color: #166534;
  }

  .nav-icon {
    font-size: 16px;
  }

  /* Sidebar Bottom & User Card Button */
  .sidebar-bottom {
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }
  
  .btn-theme-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px;
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
    border-radius: 10px;
    cursor: pointer;
    text-align: left;
    width: 100%;
    transition: background-color 0.2s, border-color 0.2s;
  }

  .user-card-btn:hover {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
  }

  .user-avatar {
    width: 32px;
    height: 32px;
    background: #0d233a;
    color: #ffffff;
    font-weight: 800;
    font-size: 12px;
    border-radius: 6px;
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
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-logout:hover {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fca5a5;
  }

  .main-workspace {
    flex: 1;
    min-width: 0;
  }

  /* Profile Modal Styles */
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
    max-width: 480px;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
  }

  .modal-title {
    font-size: 18px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 2px;
  }

  .modal-sub {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
  }

  .btn-close {
    background: none;
    border: none;
    font-size: 16px;
    color: #94a3b8;
    cursor: pointer;
    padding: 4px;
  }

  .btn-close:hover {
    color: #0d233a;
  }

  /* Avatar Section inside Modal */
  .profile-avatar-section {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 16px;
    border-radius: 12px;
    margin-bottom: 20px;
  }

  .large-avatar {
    width: 56px;
    height: 56px;
    background: #0d233a;
    color: #ffffff;
    font-size: 20px;
    font-weight: 800;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .avatar-img-lg {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .avatar-upload-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .btn-change-avatar {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #0d233a;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    display: inline-block;
    transition: background 0.2s;
  }

  .btn-change-avatar:hover {
    background: #f1f5f9;
  }

  .hidden-file-input {
    display: none;
  }

  .avatar-tip {
    font-size: 11px;
    color: #94a3b8;
    margin: 0;
  }

  .modal-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
  }

  .opt {
    font-weight: 400;
    color: #94a3b8;
  }

  .form-input {
    width: 100%;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.3s ease;
  }

  .form-input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
  }

  .btn-cancel {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
  }

  .btn-save {
    background: #15803d;
    color: #ffffff;
    border: none;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
  }

  .btn-save:hover:not(:disabled) {
    background: #166534;
  }

  .btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }
</style>