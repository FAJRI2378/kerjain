<script>
  // Siap dihubungkan ke state/route SvelteKit
</script>

<!-- Ambient Background Light -->
<div class="relative min-h-screen bg-slate-950 text-white flex flex-col justify-between overflow-hidden selection:bg-emerald-500 selection:text-white">
  
  <!-- Glow Blur Decoration -->
  <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-emerald-500/20 blur-[120px] rounded-full pointer-events-none"></div>
  <div class="absolute bottom-0 right-0 w-[400px] h-[200px] bg-purple-500/10 blur-[100px] rounded-full pointer-events-none"></div>

  <!-- Glassmorphism Navbar -->
  <header class="relative z-10 w-full max-w-7xl mx-auto px-6 py-5 flex justify-between items-center">
    <div class="flex items-center gap-2">
      <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-slate-950 text-sm shadow-lg shadow-emerald-500/30">
        K
      </div>
      <span class="font-extrabold text-lg tracking-tight text-white">KERJAIN<span class="text-emerald-400">.</span></span>
    </div>

    <div class="flex items-center gap-3">
      <a href="/login" class="px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white transition">Masuk</a>
      <a href="/register" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold transition shadow-md shadow-emerald-500/20">Daftar</a>
    </div>
  </header>

  <!-- Hero Content -->
  <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 py-12 text-center">
    <div class="max-w-3xl space-y-6">
      
      <!-- Pill Badge -->
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-slate-800 text-emerald-400 text-xs font-medium backdrop-blur-md shadow-inner">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        Platform Micro-Task Indonesia #1
      </div>

      <!-- Main Headline -->
      <h1 class="text-4xl sm:text-5xl md:text-6xl font-black tracking-tight leading-[1.1]">
        Kerja Kecil, Dampak Besar <br class="hidden sm:inline" />
        Bersama <span class="bg-gradient-to-r from-emerald-400 to-teal-300 bg-clip-text text-transparent">KERJAIN</span>
      </h1>

      <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
        Hubungkan bisnis lokal dengan talenta hebat di sekitarmu secara cepat, aman, dan transparan.
      </p>

      <!-- Role Selector Portal -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 text-left">
        
        <!-- Freelancer Card -->
        <a href="/freelancer/jobs" class="group relative p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-emerald-500/50 hover:bg-slate-900 transition-all duration-300 backdrop-blur-xl">
          <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold mb-3 group-hover:scale-110 group-hover:bg-emerald-500 group-hover:text-slate-950 transition duration-300">
            🛠️
          </div>
          <h3 class="font-bold text-base text-white group-hover:text-emerald-400 transition">Freelancer</h3>
          <p class="text-xs text-slate-400 mt-1 leading-relaxed">Cari tugas mikro sekitar dan langsung hasilkan uang.</p>
        </a>

        <!-- UMKM Card -->
        <a href="/hirer/dashboard" class="group relative p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-blue-500/50 hover:bg-slate-900 transition-all duration-300 backdrop-blur-xl">
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center font-bold mb-3 group-hover:scale-110 group-hover:bg-blue-500 group-hover:text-slate-950 transition duration-300">
            🏢
          </div>
          <h3 class="font-bold text-base text-white group-hover:text-blue-400 transition">UMKM / Hirer</h3>
          <p class="text-xs text-slate-400 mt-1 leading-relaxed">Buka lowongan tugas singkat & cari bantuan fleksibel.</p>
        </a>

        <!-- Admin Card -->
        <a href="/admin/dashboard" class="group relative p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 hover:border-purple-500/50 hover:bg-slate-900 transition-all duration-300 backdrop-blur-xl">
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center font-bold mb-3 group-hover:scale-110 group-hover:bg-purple-500 group-hover:text-slate-950 transition duration-300">
            ⚡
          </div>
          <h3 class="font-bold text-base text-white group-hover:text-purple-400 transition">Admin</h3>
          <p class="text-xs text-slate-400 mt-1 leading-relaxed">Moderasi platform, pengguna, dan kelola transaksi.</p>
        </a>

      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 text-center text-xs text-slate-600 border-t border-slate-900">
    © 2026 KERJAIN. Dibuat untuk UMKM & Talenta Lokal.
  </footer>
</div>