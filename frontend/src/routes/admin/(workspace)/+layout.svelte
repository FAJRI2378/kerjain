<script>
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { initials } from '$lib/format.js';
  let { children } = $props();

  // Menu navigasi admin
  const navItems = [
    { name: 'Dashboard', path: '/admin/dashboard', icon: '📊' },
    { name: 'Moderasi Job', path: '/admin/jobs', icon: '📝' },
    { name: 'Verifikasi User', path: '/admin/users', icon: '🪪' },
    { name: 'Laporan & Analytics', path: '/admin/analytics', icon: '📈' },
    { name: 'Pengaturan System', path: '/admin/settings', icon: '⚙️' },
    { name: 'Profil', path: '/admin/profile', icon: '👤' }
  ];

  let isMobileMenuOpen = $state(false);
  let isDarkMode = $state(true); // Default admin gelap

  onMount(() => {
    if (!auth.hydrated) auth.hydrate();
    
    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'light') {
        isDarkMode = false;
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
      else if (auth.user.role !== 'admin') goto('/', { replaceState: true });
    }
  });

  async function handleLogout() {
    await auth.logout();
    goto('/');
  }
</script>

<div class="admin-layout-wrapper min-h-screen bg-slate-950 text-slate-100 flex flex-col md:flex-row selection:bg-purple-500 selection:text-white relative">

  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between p-4 bg-slate-900/90 border-b border-slate-800 backdrop-blur-xl sticky top-0 z-50 mobile-topbar">
    <a href="/admin/dashboard" class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-xl overflow-hidden bg-purple-600/20 border border-purple-500/30 flex items-center justify-center shadow-lg shadow-purple-500/20 flex-shrink-0">
        <img src="/images/kerjain.webp" alt="Logo Kerjain" class="w-full h-full object-cover" />
      </div>
      <span class="font-extrabold text-sm tracking-tight text-white flex items-center">
        KERJAIN 
        <span class="text-[10px] text-purple-400 bg-purple-500/10 border border-purple-500/20 px-1.5 py-0.5 rounded ml-1.5 font-mono">ADMIN</span>
      </span>
    </a>
    <button 
      onclick={() => isMobileMenuOpen = !isMobileMenuOpen}
      class="px-3 py-1.5 bg-slate-800 text-slate-300 rounded-lg text-xs font-bold hover:text-white transition btn-toggle-menu flex items-center gap-1.5"
    >
      <span>{isMobileMenuOpen ? '✕' : '☰'}</span>
      <span>{isMobileMenuOpen ? 'Tutup' : 'Menu'}</span>
    </button>
  </div>

  <!-- Overlay Gelap untuk Mobile saat Sidebar Terbuka -->
  {#if isMobileMenuOpen}
    <div 
      class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 md:hidden transition-opacity"
      onclick={() => isMobileMenuOpen = false}
    ></div>
  {/if}

  <!-- Sidebar Navigasi -->
  <aside class={`fixed md:sticky top-0 left-0 h-screen w-72 bg-slate-900/95 md:bg-slate-900/90 border-r border-slate-800/80 backdrop-blur-2xl p-5 flex flex-col justify-between z-50 transition-transform duration-300 ease-in-out sidebar-panel shadow-2xl md:shadow-none ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}`}>
    
    <div class="space-y-8">
      <!-- Logo Brand & Tombol Close khusus Mobile di dalam Sidebar -->
      <div class="flex items-center justify-between px-2">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl overflow-hidden bg-purple-600/20 border border-purple-500/30 flex items-center justify-center shadow-lg shadow-purple-500/20 flex-shrink-0">
            <img src="/images/kerjain.webp" alt="Logo Kerjain" class="w-full h-full object-cover" />
          </div>
          <div>
            <span class="font-black text-base tracking-tight text-white block leading-none">KERJAIN<span class="text-purple-400">.</span></span>
            <span class="text-[9px] font-mono font-bold text-purple-400 tracking-wider uppercase">Admin Workspace</span>
          </div>
        </div>
        <!-- Tombol Close di dalam panel sidebar mobile -->
        <button 
          onclick={() => isMobileMenuOpen = false}
          class="md:hidden w-8 h-8 rounded-lg bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-sm font-bold transition"
        >
          ✕
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="space-y-1.5">
        <p class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
        {#each navItems as item}
          {@const isActive = $page.url.pathname === item.path}
          <a 
            href={item.path}
            onclick={() => isMobileMenuOpen = false}
            class={`flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition duration-200 ${
              isActive 
                ? 'bg-purple-600/15 text-purple-300 border border-purple-500/30 shadow-lg shadow-purple-500/5 nav-item-active' 
                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 nav-item-normal'
            }`}
          >
            <span class="text-base">{item.icon}</span>
            <span>{item.name}</span>
          </a>
        {/each}
      </nav>
    </div>

    <!-- Bottom Controls & Logout Bar -->
    <div class="pt-4 border-t border-slate-800/80 space-y-3">
      
      <!-- Tombol Toggle Dark/Light Mode -->
      <button 
        class="w-full py-2.5 px-3 bg-slate-800/40 hover:bg-slate-800 border border-slate-700/50 text-slate-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 btn-theme-toggle"
        onclick={() => isDarkMode = !isDarkMode}
        title="Ubah Tema"
      >
        {isDarkMode ? '🌞 Mode Terang' : '🌙 Mode Gelap'}
      </button>

      <!-- Admin Profile Info -->
      <a href="/admin/profile" onclick={() => isMobileMenuOpen = false} class="flex items-center gap-3 px-2 py-1.5 rounded-xl bg-slate-950/60 border border-slate-800/50 admin-user-box hover:border-purple-500/40 transition">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-500 to-indigo-500 flex items-center justify-center font-bold text-white text-xs flex-shrink-0">
          {auth.user ? initials(auth.user.name) : '?'}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-white truncate">{auth.user?.name ?? 'Super Admin'}</p>
          <p class="text-[10px] text-slate-500 truncate">{auth.user?.email ?? ''}</p>
        </div>
      </a>

      <!-- Exit Button -->
      <button
        onclick={handleLogout}
        class="flex items-center justify-center gap-2 w-full py-2 bg-slate-800/50 hover:bg-slate-800 text-slate-400 hover:text-rose-400 text-xs font-semibold rounded-xl border border-slate-800 transition duration-200 btn-logout-admin"
      >
        <span>👈</span> Keluar
      </button>
    </div>
  </aside>

  <!-- Main Content Container -->
  <main class="flex-1 min-w-0 overflow-y-auto main-content-area">
    {@render children()}
  </main>

</div>

<style>
  /* Light Theme Overrides for Admin Layout */
  :global(body:not(.dark-theme)) .admin-layout-wrapper {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .sidebar-panel,
  :global(body:not(.dark-theme)) .mobile-topbar {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .sidebar-panel .text-white,
  :global(body:not(.dark-theme)) .mobile-topbar .text-white {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .sidebar-panel .text-slate-400 {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .sidebar-panel nav a:hover {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .admin-user-box {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .admin-user-box .text-white {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .btn-theme-toggle {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .btn-theme-toggle:hover {
    background-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .btn-logout-admin {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .btn-toggle-menu {
    background-color: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
  }
</style>