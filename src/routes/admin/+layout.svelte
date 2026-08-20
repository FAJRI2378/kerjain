<script>
  import { page } from '$app/stores';
  let { children } = $props();

  // Menu navigasi admin
  const navItems = [
    { name: 'Dashboard', path: '/admin/dashboard', icon: '📊' },
    { name: 'Moderasi Job', path: '/admin/jobs', icon: '📝' },
    { name: 'Verifikasi User', path: '/admin/users', icon: '🪪' },
    { name: 'Laporan & Analytics', path: '/admin/analytics', icon: '📈' },
    { name: 'Pengaturan System', path: '/admin/settings', icon: '⚙️' }
  ];

  let isMobileMenuOpen = $state(false);
</script>

<div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col md:flex-row selection:bg-purple-500 selection:text-white">

  <!-- Mobile Top Bar -->
  <div class="md:hidden flex items-center justify-between p-4 bg-slate-900/90 border-b border-slate-800 backdrop-blur-xl sticky top-0 z-50">
    <a href="/admin/dashboard" class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-purple-600 flex items-center justify-center font-black text-white text-xs shadow-md shadow-purple-500/20">
        K
      </div>
      <span class="font-extrabold text-sm tracking-tight text-white">KERJAIN <span class="text-[10px] text-purple-400 bg-purple-500/10 border border-purple-500/20 px-1.5 py-0.5 rounded ml-1 font-mono">ADMIN</span></span>
    </a>
    <button 
      onclick={() => isMobileMenuOpen = !isMobileMenuOpen}
      class="p-2 bg-slate-800 text-slate-300 rounded-lg text-xs hover:text-white"
    >
      {isMobileMenuOpen ? '✕ Close' : '☰ Menu'}
    </button>
  </div>

  <!-- Sidebar Navigasi -->
  <aside class={`fixed md:sticky top-0 left-0 h-screen w-64 bg-slate-900/90 border-r border-slate-800/80 backdrop-blur-2xl p-5 flex flex-col justify-between z-40 transition-transform duration-300 ${isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}`}>
    
    <div class="space-y-8">
      <!-- Logo Brand -->
      <div class="hidden md:flex items-center gap-2.5 px-2">
        <div class="w-8 h-8 rounded-xl bg-purple-600 flex items-center justify-center font-black text-white text-sm shadow-lg shadow-purple-500/30">
          K
        </div>
        <div>
          <span class="font-black text-base tracking-tight text-white block leading-none">KERJAIN<span class="text-purple-400">.</span></span>
          <span class="text-[9px] font-mono font-bold text-purple-400 tracking-wider uppercase">Admin Workspace</span>
        </div>
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
                ? 'bg-purple-600/15 text-purple-300 border border-purple-500/30 shadow-lg shadow-purple-500/5' 
                : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
            }`}
          >
            <span class="text-base">{item.icon}</span>
            <span>{item.name}</span>
          </a>
        {/each}
      </nav>
    </div>

    <!-- Bottom Profile & Logout Bar -->
    <div class="pt-4 border-t border-slate-800/80 space-y-3">
      <!-- Admin Profile Info -->
      <div class="flex items-center gap-3 px-2 py-1.5 rounded-xl bg-slate-950/60 border border-slate-800/50">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-purple-500 to-indigo-500 flex items-center justify-center font-bold text-white text-xs">
          AD
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-white truncate">Super Admin</p>
          <p class="text-[10px] text-slate-500 truncate">admin@kerjain.id</p>
        </div>
      </div>

      <!-- Exit Button -->
      <a 
        href="/" 
        class="flex items-center justify-center gap-2 w-full py-2 bg-slate-800/50 hover:bg-slate-800 text-slate-400 hover:text-rose-400 text-xs font-semibold rounded-xl border border-slate-800 transition duration-200"
      >
        <span>👈</span> Keluar ke Beranda
      </a>
    </div>
  </aside>

  <!-- Main Content Container -->
  <main class="flex-1 min-w-0 overflow-y-auto">
    {@render children()}
  </main>

</div>