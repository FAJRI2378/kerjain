<script>
  let timeframe = $state('monthly'); // 'weekly', 'monthly', 'yearly'

  let analyticsData = $state({
    totalTransactions: 'Rp 48.500.000',
    platformCommission: 'Rp 4.850.000', // 10% fee
    completedJobs: 142,
    activeUsers: 1240
  });
</script>

<div class="p-6 md:p-10 space-y-8 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
    <div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Laporan & Analytics</h1>
      <p class="text-xs md:text-sm text-slate-400">Pantau pertumbuhan pengguna, transaksi, dan statistik pendapatan platform.</p>
    </div>
    
    <!-- Timeframe Filter -->
    <div class="flex gap-1.5 bg-slate-900 p-1 border border-slate-800 rounded-xl text-xs">
      <button 
        onclick={() => timeframe = 'weekly'}
        class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'weekly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Mingguan
      </button>
      <button 
        onclick={() => timeframe = 'monthly'}
        class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'monthly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Bulanan
      </button>
      <button 
        onclick={() => timeframe = 'yearly'}
        class={`px-3 py-1.5 rounded-lg transition ${timeframe === 'yearly' ? 'bg-purple-600 text-white font-bold' : 'text-slate-400 hover:text-white'}`}
      >
        Tahunan
      </button>
    </div>
  </div>

  <!-- Key Metrics Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Perputaran GMV</span>
      <p class="text-2xl font-black text-white">{analyticsData.totalTransactions}</p>
      <p class="text-[10px] text-emerald-400 font-semibold">📈 +15% dibanding bulan lalu</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pendapatan Platform (Fee)</span>
      <p class="text-2xl font-black text-purple-400">{analyticsData.platformCommission}</p>
      <p class="text-[10px] text-purple-300 font-semibold">Take-rate komisi 10%</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas Selesai</span>
      <p class="text-2xl font-black text-white">{analyticsData.completedJobs} Tugas</p>
      <p class="text-[10px] text-emerald-400 font-semibold">Success rate 98%</p>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-2">
      <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengguna Aktif</span>
      <p class="text-2xl font-black text-white">{analyticsData.activeUsers}</p>
      <p class="text-[10px] text-blue-400 font-semibold">Freelancer & UMKM</p>
    </div>
  </div>

  <!-- Performance Charts Visual Placeholders -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Chart 1: Transaksi -->
    <div class="lg:col-span-2 p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <div class="flex justify-between items-center">
        <div>
          <h3 class="font-bold text-white text-sm">Tren Transaksi & Revenue</h3>
          <p class="text-xs text-slate-400">Volume pekerjaan yang berhasil diselesaikan per bulan.</p>
        </div>
      </div>
      <!-- Dummy Bar Chart Visual -->
      <div class="h-48 flex items-end justify-between gap-2 pt-6 border-b border-slate-800 px-2">
        <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t h-[40%] transition group relative">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition">Jan</span>
        </div>
        <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t h-[55%] transition group relative">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition">Feb</span>
        </div>
        <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t h-[35%] transition group relative">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition">Mar</span>
        </div>
        <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t h-[70%] transition group relative">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition">Apr</span>
        </div>
        <div class="w-full bg-purple-500/20 hover:bg-purple-500/40 rounded-t h-[85%] transition group relative">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-slate-400 opacity-0 group-hover:opacity-100 transition">Mei</span>
        </div>
        <div class="w-full bg-purple-500/80 rounded-t h-[100%] transition group relative shadow-lg shadow-purple-500/20">
          <span class="absolute -top-6 left-1/2 -translate-x-1/2 text-[10px] text-purple-300 font-bold">Jun</span>
        </div>
      </div>
      <div class="flex justify-between text-[10px] text-slate-500">
        <span>Januari</span>
        <span>Juni (Puncak)</span>
      </div>
    </div>

    <!-- Chart 2: Komposisi Peran -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <h3 class="font-bold text-white text-sm">Distribusi Kategori Tugas</h3>
      <p class="text-xs text-slate-400">Kategori paling diminati UMKM.</p>
      
      <div class="space-y-3 pt-2 text-xs">
        <div>
          <div class="flex justify-between text-slate-300 mb-1">
            <span>Pemasaran & Diskon</span>
            <span class="font-bold text-purple-400">42%</span>
          </div>
          <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
            <div class="bg-purple-500 h-full rounded-full" style="width: 42%;"></div>
          </div>
        </div>

        <div>
          <div class="flex justify-between text-slate-300 mb-1">
            <span>Desain Grafis & Foto</span>
            <span class="font-bold text-emerald-400">30%</span>
          </div>
          <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
            <div class="bg-emerald-500 h-full rounded-full" style="width: 30%;"></div>
          </div>
        </div>

        <div>
          <div class="flex justify-between text-slate-300 mb-1">
            <span>Input Data & Admin</span>
            <span class="font-bold text-blue-400">18%</span>
          </div>
          <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
            <div class="bg-blue-500 h-full rounded-full" style="width: 18%;"></div>
          </div>
        </div>

        <div>
          <div class="flex justify-between text-slate-300 mb-1">
            <span>Lainnya</span>
            <span class="font-bold text-slate-400">10%</span>
          </div>
          <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
            <div class="bg-slate-600 h-full rounded-full" style="width: 10%;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>