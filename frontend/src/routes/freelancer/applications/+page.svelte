<script>
  import { onMount } from 'svelte';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let loading = $state(true);
  let activeFilter = $state("Semua");
  let applications = $state([]);

  // Simulasi data lamaran masuk dari backend
  async function loadApplications() {
    loading = true;
    try {
      // Simulasi request API
      await new Promise(resolve => setTimeout(resolve, 400));
      
      applications = [
        {
          id: 101,
          applied_at: '2026-09-04T09:30:00Z',
          status: 'accepted', // accepted | pending | rejected
          job: {
            id: 2,
            title: 'Desain Poster Promo Grand Opening (A3)',
            employer: 'Kopi Kenangan Lokal',
            category: 'Desain',
            reward: 50000,
            location: 'Depok'
          },
          message: 'Selamat! Pemilik UMKM telah memilih Anda. Silakan mulai kerjakan tugas sesuai tenggat waktu.'
        },
        {
          id: 102,
          applied_at: '2026-09-05T14:15:00Z',
          status: 'pending',
          job: {
            id: 1,
            title: 'Foto Produk 20 Menu + Upload ke Gofood',
            employer: 'Soto Ayam Pak Budi',
            category: 'Konten & Media',
            reward: 75000,
            location: 'Jakarta Selatan'
          },
          message: 'Lamaran Anda sedang ditinjau oleh pemilik UMKM.'
        },
        {
          id: 103,
          applied_at: '2026-09-01T08:00:00Z',
          status: 'rejected',
          job: {
            id: 4,
            title: 'Bantu Packing 100 Box Snack Box',
            employer: 'Dapur Mama Snack',
            category: 'Operasional',
            reward: 60000,
            location: 'Kukusan, Depok'
          },
          message: 'Maaf, UMKM telah memilih talenta lain yang lebih sesuai dengan kebutuhan lokasi.'
        }
      ];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat status lamaran.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(loadApplications);

  // Filter reaktif berdasarkan tab status
  let filteredApplications = $derived(
    activeFilter === "Semua" 
      ? applications 
      : applications.filter(app => app.status === activeFilter)
  );

  // Fungsi batalkan lamaran (opsional interaktif)
  function cancelApplication(appId) {
    applications = applications.filter(app => app.id !== appId);
    toast('Lamaran berhasil dibatalkan.', 'success');
  }
</script>

<svelte:head>
  <title>Status Lamaran - Freelancer Portal</title>
</svelte:head>

<div class="p-6 md:p-10 space-y-6 font-sans applications-page max-w-5xl mx-auto">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 header-box">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Portal Freelancer</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight page-title">Status Lamaran Saya</h1>
      <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 page-sub">Pantau proses seleksi tugas mikromu dari berbagai UMKM lokal.</p>
    </div>
    
    <a href="/freelancer/jobs" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 flex-shrink-0">
      <span>🔍</span> Cari Tugas Lain
    </a>
  </div>

  <div class="flex gap-2 overflow-x-auto pb-1">
    {#each [
      { label: 'Semua', val: 'Semua' },
      { label: '🎉 Diterima', val: 'accepted' },
      { label: '⏳ Menunggu', val: 'pending' },
      { label: '❌ Ditolak', val: 'rejected' }
    ] as filter}
      <button 
        onclick={() => activeFilter = filter.val}
        class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap border
          {activeFilter === filter.val 
            ? 'bg-slate-900 text-white border-slate-900 dark:bg-white dark:text-slate-900 dark:border-white shadow-sm' 
            : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800 dark:hover:bg-slate-800 filter-btn'}"
      >
        {filter.label}
      </button>
    {/each}
  </div>

  <div class="space-y-4">
    {#if loading}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 text-xs shadow-sm">
        Memuat data lamaran...
      </div>
    {:else if filteredApplications.length === 0}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3 empty-box shadow-sm">
        <p class="text-3xl">📭</p>
        <p class="font-bold text-slate-900 dark:text-white text-base text-dark-fix">Tidak ada riwayat lamaran</p>
        <p class="text-xs text-slate-500 dark:text-slate-400">Kamu belum memiliki riwayat lamaran dengan kategori status ini.</p>
      </div>
    {:else}
      {#each filteredApplications as app (app.id)}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-5 app-item-card">
          
          <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              {#if app.status === 'accepted'}
                <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
                  🎉 DITERIMA UMKM
                </span>
              {:else if app.status === 'rejected'}
                <span class="px-2.5 py-0.5 bg-red-500/10 text-red-600 dark:text-red-400 font-extrabold text-[11px] rounded-md border border-red-500/20">
                  ❌ TIDAK DITERIMA
                </span>
              {:else}
                <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-extrabold text-[11px] rounded-md border border-amber-500/20">
                  ⏳ MENINJAU (PENDING)
                </span>
              {/if}

              <span class="text-[11px] font-mono text-slate-400">• Dilamar: {new Date(app.applied_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</span>
            </div>

            <h3 class="font-black text-base text-slate-900 dark:text-white app-title text-dark-fix leading-snug">{app.job.title}</h3>
            
            <div class="flex items-center gap-3 text-xs font-semibold text-slate-600 dark:text-slate-300">
              <span>🏢 {app.job.employer}</span>
              <span>•</span>
              <span class="text-emerald-600 dark:text-emerald-400 font-bold">{formatRupiah(app.job.reward)}</span>
            </div>

            <div class="text-[11px] p-3 rounded-xl feedback-box font-medium leading-relaxed">
              <strong>Catatan:</strong> "{app.message}"
            </div>
          </div>

          <div class="w-full md:w-auto flex items-center justify-end gap-2 pt-3 md:pt-0 border-t md:border-0 border-slate-100 dark:border-slate-800">
            {#if app.status === 'accepted'}
              <a href="/freelancer/mytasks" class="w-full md:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center">
                Mulai Kerjakan 🚀
              </a>
            {:else if app.status === 'rejected'}
              <button class="w-full md:w-auto px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-400 text-xs font-bold rounded-xl cursor-not-allowed" disabled>
                Selesai / Ditutup
              </button>
            {:else}
              <button 
                onclick={() => cancelApplication(app.id)}
                class="w-full md:w-auto px-4 py-2.5 bg-slate-100 hover:bg-red-50 dark:bg-slate-800 dark:hover:bg-red-950/30 text-slate-700 hover:text-red-600 dark:text-slate-300 dark:hover:text-red-400 text-xs font-bold rounded-xl transition border border-slate-200 dark:border-slate-700"
              >
                Batalkan Lamaran
              </button>
            {/if}
          </div>

        </div>
      {/each}
    {/if}
  </div>
</div>

<style>
  /* ---------------- LIGHT MODE CUSTOM OVERRIDES ---------------- */
  :global(body:not(.dark-theme)) .applications-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-title,
  :global(body:not(.dark-theme)) .app-title,
  :global(body:not(.dark-theme)) .text-dark-fix {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-sub {
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .header-box {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .app-item-card,
  :global(body:not(.dark-theme)) .empty-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  }

  :global(body:not(.dark-theme)) .feedback-box {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
    border: 1px solid #e2e8f0;
  }

  /* ---------------- DARK MODE CUSTOM OVERRIDES ---------------- */
  :global(body.dark-theme) .feedback-box {
    background-color: #0f172a !important;
    color: #94a3b8 !important;
    border: 1px solid #334155;
  }
</style>