<script>
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { initials } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let { children } = $props();

  const navItems = [
    { name: 'Dashboard', path: '/hire/dashboard', icon: '📊' },
    { name: 'Buat Tugas Baru', path: '/hire/jobs/create', icon: '➕' },
    { name: 'Kelola Jobs', path: '/hire/jobs', icon: '📁' },
    { name: 'Invoice & Kuitansi', path: '/hire/invoices', icon: '🧾' },
    { name: 'Chat Freelancer', path: '/hire/messages', icon: '💬' },
    { name: 'Reviews', path: '/hire/reviews', icon: '📜' },
    { name: 'Dompet Escrow', path: '/hire/wallet', icon: '💰' },
    { name: 'Profil Usaha', path: '/hire/verifikasi', icon: '🏪' }
  ];

  let isMobileMenuOpen = $state(false);
  let isProfileOpen = $state(false);
  
  // State untuk Fitur Dark Mode
  let isDarkMode = $state(false);
  
  // State Form Verifikasi Usaha & PIC
  let businessName = $state('');
  let businessAddress = $state('');
  let businessCategory = $state('Kuliner / Makanan');
  let profileName = $state('');
  let profileEmail = $state('');
  let profilePhone = $state('');
  
  let avatarPreview = $state('');
  let avatarFile = $state(null);
  
  let storePhotoPreview = $state('');
  let storePhotoFile = $state(null);

  let newPassword = $state('');
  let isSavingProfile = $state(false);

  function getUserAvatar(userObj) {
    if (!userObj) return null;
    return userObj.avatar_url || userObj.avatar || userObj.profile_photo_url || userObj.photo_url || userObj.photo || null;
  }

  onMount(() => {
    if (!auth.hydrated) auth.hydrate();
    
    // Cek preferensi tema sebelumnya (jika ada di localStorage)
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
      else if (auth.user.role !== 'hirer') goto('/', { replaceState: true });

      // Sinkronisasi data user & bisnis
      if (auth.user) {
        businessName = auth.user.business_profile?.business_name || auth.user.name || 'UMKM';
        businessAddress = auth.user.business_profile?.address || auth.user.address || '';
        businessCategory = auth.user.business_profile?.category || 'Kuliner / Makanan';
        profileName = auth.user.name || '';
        profileEmail = auth.user.email || '';
        profilePhone = auth.user.phone || '';
        
        if (!avatarFile) {
          avatarPreview = getUserAvatar(auth.user) || '';
        }
        if (!storePhotoFile) {
          storePhotoPreview = auth.user.business_profile?.store_photo || '';
        }
      }
    }
  });

  function handleAvatarChange(e) {
    const file = e.target.files[0];
    if (file) {
      avatarFile = file;
      avatarPreview = URL.createObjectURL(file);
    }
  }

  function handleStorePhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
      storePhotoFile = file;
      storePhotoPreview = URL.createObjectURL(file);
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
      formData.append('business_name', businessName);
      formData.append('address', businessAddress);
      formData.append('category', businessCategory);
      
      if (avatarFile) {
        formData.append('avatar', avatarFile);
        formData.append('photo', avatarFile);
      }

      if (storePhotoFile) {
        formData.append('store_photo', storePhotoFile);
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
      
      toast('Verifikasi & Profil Usaha berhasil diperbarui!', 'success');
      isProfileOpen = false;
      newPassword = '';
      avatarFile = null;
      storePhotoFile = null;
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyimpan data verifikasi.'), 'error');
    } finally {
      isSavingProfile = false;
    }
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
      <!-- Logo Brand -->
      <a href="/hire/dashboard" class="brand brand-desktop">
        <div class="brand-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
        </div>
        <div class="brand-text">
          <span class="brand-name">KERJAIN<span class="dot">.</span></span>
          <span class="brand-sub">BUSINESS & HIRER</span>
        </div>
      </a>

      <!-- Navigation Links -->
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

    <!-- Bottom Profile, Theme Toggle & Logout Bar -->
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
        title="Buka Verifikasi & Profil Usaha"
      >
        <div class="user-avatar">
          {#if getUserAvatar(auth.user) || avatarPreview}
            <img src={avatarPreview || getUserAvatar(auth.user)} alt="Logo Usaha" class="avatar-img" />
          {:else}
            {auth.user ? initials(businessName) : '?'}
          {/if}
        </div>
        <div class="user-info">
          <p class="user-name">{businessName}</p>
          <p class="user-status verified">🛡️ Escrow Verified</p>
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

<!-- Modal Dialog Verifikasi & Profil Usaha -->
{#if isProfileOpen}
  <div class="modal-backdrop" onclick={() => isProfileOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header">
        <div class="modal-title-group">
          <h2 class="modal-title">Verifikasi & Profil Usaha</h2>
          <p class="modal-sub">Lengkapi alamat, kategori dagang, dan foto tempat usaha Anda.</p>
        </div>
        <button class="btn-close" onclick={() => isProfileOpen = false}>✕</button>
      </div>

      <!-- Section Upload Logo & Foto Toko -->
      <div class="upload-grid">
        <div class="upload-box">
          <span class="upload-label">Logo Usaha</span>
          <div class="avatar-preview-wrap">
            {#if avatarPreview}
              <img src={avatarPreview} alt="Logo" class="preview-img" />
            {:else}
              <span class="placeholder-text">{initials(businessName)}</span>
            {/if}
          </div>
          <label for="logo-input" class="btn-upload-sm">📷 Ganti Logo</label>
          <input id="logo-input" type="file" accept="image/*" onchange={handleAvatarChange} class="hidden" />
        </div>

        <div class="upload-box">
          <span class="upload-label">Foto Tempat Dagang</span>
          <div class="store-preview-wrap">
            {#if storePhotoPreview}
              <img src={storePhotoPreview} alt="Toko" class="preview-img" />
            {:else}
              <span class="placeholder-text">Belum ada foto</span>
            {/if}
          </div>
          <label for="store-input" class="btn-upload-sm">🏪 Ganti Foto</label>
          <input id="store-input" type="file" accept="image/*" onchange={handleStorePhotoChange} class="hidden" />
        </div>
      </div>

      <form onsubmit={handleSaveProfile} class="modal-form">
        <div class="form-row-grid">
          <div class="form-group">
            <label for="prof-biz">Nama Usaha / Toko</label>
            <input id="prof-biz" type="text" bind:value={businessName} required class="form-input" />
          </div>
          <div class="form-group">
            <label for="prof-cat">Kategori Usaha</label>
            <select id="prof-cat" bind:value={businessCategory} class="form-input">
              <option value="Kuliner / Makanan">Kuliner / Makanan</option>
              <option value="Retail / Toko">Retail / Toko</option>
              <option value="Fashion / Konveksi">Fashion / Konveksi</option>
              <option value="Jasa / Lainnya">Jasa / Lainnya</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="prof-address">Alamat Lengkap Usaha</label>
          <textarea id="prof-address" rows="2" bind:value={businessAddress} required class="form-input textarea" placeholder="Jl. Raya No. 123..."></textarea>
        </div>

        <div class="form-row-grid">
          <div class="form-group">
            <label for="prof-name">Nama Pemilik / PIC</label>
            <input id="prof-name" type="text" bind:value={profileName} required class="form-input" />
          </div>
          <div class="form-group">
            <label for="prof-phone">Nomor WhatsApp</label>
            <input id="prof-phone" type="tel" bind:value={profilePhone} class="form-input" />
          </div>
        </div>

        <div class="form-group">
          <label for="prof-email">Email Akun</label>
          <input id="prof-email" type="email" bind:value={profileEmail} required class="form-input" />
        </div>

        <div class="form-group">
          <label for="prof-pass">Ubah Kata Sandi <span class="opt">(Opsional)</span></label>
          <input id="prof-pass" type="password" bind:value={newPassword} placeholder="Isi jika ingin merubah sandi" class="form-input" />
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick={() => isProfileOpen = false}>Batal</button>
          <button type="submit" disabled={isSavingProfile} class="btn-save">
            {isSavingProfile ? 'Menyimpan...' : 'Simpan Verifikasi'}
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
  :global(body.dark-theme .upload-box) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }

  :global(body.dark-theme .brand-name),
  :global(body.dark-theme .modal-title),
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .user-name) {
    color: #ffffff !important;
  }

  :global(body.dark-theme .brand-sub),
  :global(body.dark-theme .modal-sub),
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .opt),
  :global(body.dark-theme .placeholder-text),
  :global(body.dark-theme .upload-label) {
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
  :global(body.dark-theme .form-input),
  :global(body.dark-theme select.form-input),
  :global(body.dark-theme textarea.form-input) {
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
  :global(body.dark-theme .btn-upload-sm),
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
  :global(body.dark-theme .card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
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

  /* Sidebar */
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

  /* Sidebar Bottom & User Card Button */
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
    color: #15803d;
    margin: 1px 0 0;
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

  /* Profile Modal Styles */
  .modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 100;
    overflow-y: auto;
  }

  .modal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    width: 100%;
    max-width: 560px;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    max-height: 90vh;
    overflow-y: auto;
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
    color: #0f172a;
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
    color: #64748b;
    cursor: pointer;
  }

  .btn-close:hover {
    color: #0f172a;
  }

  /* Upload Grid inside Modal */
  .upload-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
  }

  .upload-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 12px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    transition: background-color 0.3s, border-color 0.3s;
  }

  .upload-label {
    font-size: 11px;
    font-weight: 700;
    color: #334155;
  }

  .avatar-preview-wrap, .store-preview-wrap {
    width: 56px;
    height: 56px;
    background: #e2e8f0;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    font-weight: 800;
    color: #15803d;
  }

  .store-preview-wrap {
    width: 100%;
    height: 72px;
  }

  .preview-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .placeholder-text {
    font-size: 10px;
    color: #64748b;
  }

  .btn-upload-sm {
    background: #e2e8f0;
    color: #0f172a;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
    cursor: pointer;
    border: 1px solid #cbd5e1;
  }

  .btn-upload-sm:hover {
    background: #cbd5e1;
  }

  .hidden {
    display: none;
  }

  .modal-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .form-row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group label {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
  }

  .opt {
    font-weight: 400;
    color: #64748b;
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
    transition: border-color 0.2s, background-color 0.3s;
  }

  .form-input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.15);
  }

  .textarea {
    resize: vertical;
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
  }

  .btn-cancel {
    background: #e2e8f0;
    border: 1px solid #cbd5e1;
    color: #0f172a;
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
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.2);
  }

  .btn-save:hover:not(:disabled) {
    background: #166534;
  }

  .btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
</style>