<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  // --- STATE UTAMA ---
  let jobs = $state([]);
  let applicantsDB = $state({}); 
  let loading = $state(true);

  // --- STATE MODAL PELAMAR ---
  let selectedJobApplicants = $state([]);
  let isApplicantsModalOpen = $state(false);
  let activeJobId = $state(null);
  let activeJobTitle = $state('');
  let loadingApplicants = $state(false);

  let selectedFreelancer = $state(null);
  let isProfileModalOpen = $state(false);

  // --- STATE MODAL EDIT JOB ---
  let isEditModalOpen = $state(false);
  let editJobId = $state(null);
  let editTitle = $state('');
  let editCategory = $state('');
  let editBudget = $state('');
  let isSubmittingEdit = $state(false);

  // --- INISIALISASI DATA DUMMY ---
  async function loadJobs() {
    loading = true;
    try {
      throw new Error("Gunakan Dummy"); 
    } catch (err) {
      jobs = [
        {
          id: 1,
          title: 'Desain Feed Instagram & Banner Promo Kopi Senja',
          category: 'Kuliner / F&B',
          budget: 150000,
          status: 'active',
          rejection_reason: null,
          applicants_count: 3,
          created_at: '2026-09-05T10:00:00Z'
        },
        {
          id: 2,
          title: 'Pembuatan Video Reels TikTok untuk Grand Opening Toko',
          category: 'Kuliner / F&B',
          budget: 750000,
          status: 'rejected',
          rejection_reason: 'Deskripsi tugas kurang lengkap dan detail budget fee belum memenuhi standar operasional minimal platform.',
          applicants_count: 0,
          created_at: '2026-09-06T14:30:00Z'
        }
      ];

      applicantsDB = {
        1: [
          {
            id: 101,
            name: 'Siti Rahmawati',
            email: 'siti.rahma@gmail.com',
            rank: 'Expert Level 2',
            rating: 4.9,
            completed_tasks: 42,
            skills: ['Copywriting', 'Instagram Ads', 'Design Feed'],
            avatar: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=200&q=80',
            cover_letter: 'Halo, saya berpengalaman mengelola sosmed F&B lebih dari 3 tahun dan siap menaikkan engagement toko Anda.'
          }
        ],
        2: []
      };
    } finally {
      loading = false;
    }
  }

  onMount(loadJobs);

  function getCategoryName(category) {
    if (!category) return 'Umum';
    if (typeof category === 'object') return category.name || 'Umum';
    return category;
  }

  // --- LOGIKA MODAL PELAMAR ---
  function viewApplicants(job) {
    activeJobId = job.id;
    activeJobTitle = job.title;
    isApplicantsModalOpen = true;
    loadingApplicants = true;
    
    setTimeout(() => {
      selectedJobApplicants = applicantsDB[activeJobId] || [];
      loadingApplicants = false;
    }, 400);
  }

  function openFreelancerProfile(freelancer) {
    selectedFreelancer = freelancer;
    isProfileModalOpen = true;
  }

  function acceptFreelancer(freelancer) {
    toast(`Berhasil memilih ${freelancer.name}! Tugas segera dimulai.`, 'success'); 
    isProfileModalOpen = false; 
    isApplicantsModalOpen = false; 
  }

  function rejectFreelancer(freelancer) {
    if (applicantsDB[activeJobId]) {
      applicantsDB[activeJobId] = applicantsDB[activeJobId].filter(a => a.id !== freelancer.id);
    }
    selectedJobApplicants = applicantsDB[activeJobId];
    
    const jobIndex = jobs.findIndex(j => j.id === activeJobId);
    if (jobIndex !== -1 && jobs[jobIndex].applicants_count > 0) {
      jobs[jobIndex].applicants_count -= 1;
    }

    toast(`Pelamar ${freelancer.name} telah ditolak.`, 'error');
    isProfileModalOpen = false; 
  }

  // --- FITUR BARU: EDIT JOB ---
  function openEditModal(job) {
    editJobId = job.id;
    editTitle = job.title;
    editCategory = getCategoryName(job.category);
    editBudget = job.budget;
    isEditModalOpen = true;
  }

  async function submitEditJob(e) {
    e.preventDefault();
    isSubmittingEdit = true;

    try {
      // Simulasi API Update
      await new Promise(r => setTimeout(r, 400));
      
      const index = jobs.findIndex(j => j.id === editJobId);
      if (index !== -1) {
        jobs[index].title = editTitle;
        jobs[index].category = editCategory;
        jobs[index].budget = Number(editBudget);
        
        // Jika sebelumnya ditolak admin, ubah kembali jadi pending_moderation atau active setelah diedit
        if (jobs[index].status === 'rejected') {
          jobs[index].status = 'pending_moderation';
          jobs[index].rejection_reason = null;
        }
      }

      toast('Tugas berhasil diperbarui dan diajukan ulang!', 'success');
      isEditModalOpen = false;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memperbarui tugas.'), 'error');
    } finally {
      isSubmittingEdit = false;
    }
  }

  // --- FITUR BARU: HAPUS JOB ---
  async function deleteJob(jobId) {
    if (!confirm('Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.')) return;

    try {
      // Simulasi API Delete: await api.delete(`/api/hire/jobs/${jobId}`);
      await new Promise(r => setTimeout(r, 300));
      
      jobs = jobs.filter(j => j.id !== jobId);
      delete applicantsDB[jobId];

      toast('Tugas berhasil dihapus.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menghapus tugas.'), 'error');
    }
  }

  const totalApplicantsAll = $derived(jobs.reduce((sum, j) => sum + (j.applicants_count || 0), 0));
</script>

<div class="p-6 md:p-10 space-y-8 font-sans hire-jobs-page max-w-7xl mx-auto">
  
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 header-box">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Workspace UMKM</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight page-title text-dark-fix">Kelola Jobs & Tugas</h1>
      <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 page-sub text-dark-sub">Pantau tugas yang diposting, tinjau pelamar, dan pilih freelancer terbaik.</p>
    </div>
    
    <a href="/hire/jobs/create" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 flex-shrink-0">
      <span>➕</span> Buat Tugas Baru
    </a>
  </div>

  <!-- Metric Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Total Tugas</p>
        <p class="text-2xl font-black text-slate-900 dark:text-white text-dark-fix">{jobs.length}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold">📋</div>
    </div>

    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Total Pelamar Masuk</p>
        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{totalApplicantsAll}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg font-bold">👥</div>
    </div>

    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Status Postingan</p>
        <p class="text-2xl font-black text-slate-900 dark:text-white text-dark-fix">Kelola</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold">⚡</div>
    </div>
  </div>

  <!-- Daftar Job Utama -->
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h2 class="font-bold text-sm text-slate-900 dark:text-white text-dark-fix">Daftar Job Terpublikasi</h2>
      <span class="text-xs text-slate-500 dark:text-slate-400 text-dark-sub">{jobs.length} Tugas Ditemukan</span>
    </div>

    {#if loading}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 text-xs empty-box">
        Sedang memuat data pekerjaan...
      </div>
    {:else if jobs.length === 0}
      <div class="p-12 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl space-y-3 empty-box">
        <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-2xl empty-icon-bg">📁</div>
        <p class="font-bold text-slate-900 dark:text-white text-base text-dark-fix">Belum ada tugas diposting</p>
        <p class="text-xs text-slate-500 dark:text-slate-400 text-dark-sub max-w-sm mx-auto">Mulai buat tugas pertama Anda untuk mempekerjakan freelancer terverifikasi.</p>
      </div>
    {:else}
      {#each jobs as job (job.id)}
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-5 job-item-card">
          
          <div class="space-y-2 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
                {getCategoryName(job.category)}
              </span>
              <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 text-dark-sub">ID: #{job.id}</span>
              {#if job.created_at}
                <span class="text-[11px] text-slate-500 dark:text-slate-400 text-dark-sub">• {new Date(job.created_at).toLocaleDateString('id-ID')}</span>
              {/if}

              <!-- Indikator Status Job -->
              {#if job.status === 'rejected'}
                <span class="px-2 py-0.5 bg-red-500/10 text-red-600 dark:text-red-400 font-black text-[10px] rounded border border-red-500/20">
                  ❌ Ditolak Admin
                </span>
              {:else if job.status === 'pending_moderation'}
                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 font-black text-[10px] rounded border border-amber-500/20">
                  ⏳ Menunggu Review Admin
                </span>
              {:else}
                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black text-[10px] rounded border border-emerald-500/20">
                  ✅ Aktif / Disetujui
                </span>
              {/if}
            </div>

            <h3 class="font-black text-base text-slate-900 dark:text-white job-title text-dark-fix leading-snug">{job.title}</h3>
            
            <div class="flex items-center gap-4 text-xs font-bold text-emerald-600 dark:text-emerald-400">
              <span>Budget Escrow: {formatRupiah(job.budget)}</span>
            </div>

            <!-- Kotak Alasan Penolakan oleh Admin -->
            {#if job.status === 'rejected' && job.rejection_reason}
              <div class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 text-xs text-red-700 dark:text-red-300 space-y-1.5">
                <p class="font-bold flex items-center gap-1.5">
                  <span>⚠️</span> Alasan Tugas Ditolak oleh Admin:
                </p>
                <p class="italic text-red-600 dark:text-red-400 leading-relaxed">
                  "{job.rejection_reason}"
                </p>
                <div class="pt-1 flex items-center gap-3">
                  <button onclick={() => openEditModal(job)} class="font-bold underline hover:text-red-800 dark:hover:text-red-200 text-left">
                    Perbaiki & Ajukan Ulang &rarr;
                  </button>
                </div>
              </div>
            {/if}
          </div>

          <!-- Tombol Aksi (Lihat Pelamar, Edit, Hapus) -->
          <div class="flex items-center gap-2.5 w-full md:w-auto justify-between md:justify-end pt-3 md:pt-0 border-t md:border-0 border-slate-100 dark:border-slate-800 divider-border">
            {#if job.status !== 'rejected'}
              <button 
                onclick={() => viewApplicants(job)}
                class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-sm btn-applicants"
              >
                <span>👥</span>
                <span>{job.applicants_count ?? 0} Pelamar</span>
              </button>
            {/if}

            <!-- Tombol Edit -->
            <button 
              onclick={() => openEditModal(job)}
              class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
              title="Edit Tugas"
            >
              <span>✏️</span> Edit
            </button>

            <!-- Tombol Hapus -->
            <button 
              onclick={() => deleteJob(job.id)}
              class="px-3.5 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 text-red-600 dark:text-red-400 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
              title="Hapus Tugas"
            >
              <span>🗑️</span> Hapus
            </button>
          </div>
        </div>
      {/each}
    {/if}
  </div>
</div>

<!-- Modal Edit Job -->
{#if isEditModalOpen}
  <div class="modal-backdrop" onclick={() => isEditModalOpen = false}>
    <div class="modal-card max-w-lg" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center divider-border">
        <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Edit & Perbarui Tugas</h2>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition" onclick={() => isEditModalOpen = false}>✕</button>
      </div>

      <form onsubmit={submitEditJob} class="space-y-4 pt-4 text-xs">
        <div class="space-y-1.5">
          <label for="edit-title" class="font-bold text-slate-700 dark:text-slate-300">Judul Tugas / Pekerjaan</label>
          <input 
            id="edit-title"
            type="text" 
            bind:value={editTitle} 
            required 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
          />
        </div>

        <div class="space-y-1.5">
          <label for="edit-cat" class="font-bold text-slate-700 dark:text-slate-300">Kategori</label>
          <input 
            id="edit-cat"
            type="text" 
            bind:value={editCategory} 
            required 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
          />
        </div>

        <div class="space-y-1.5">
          <label for="edit-budget" class="font-bold text-slate-700 dark:text-slate-300">Budget / Fee (Rp)</label>
          <input 
            id="edit-budget"
            type="number" 
            bind:value={editBudget} 
            required 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
          />
        </div>

        <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
          <button 
            type="button" 
            onclick={() => isEditModalOpen = false} 
            class="px-4 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl"
          >
            Batal
          </button>
          <button 
            type="submit" 
            disabled={isSubmittingEdit}
            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 disabled:opacity-50"
          >
            {isSubmittingEdit ? 'Menyimpan...' : 'Simpan Perubahan'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<!-- Modal 1: Daftar Pelamar -->
{#if isApplicantsModalOpen}
  <div class="modal-backdrop" onclick={() => isApplicantsModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-start divider-border">
        <div>
          <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Daftar Pelamar Tugas</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 max-w-md line-clamp-1 text-dark-sub">{activeJobTitle}</p>
        </div>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition" onclick={() => isApplicantsModalOpen = false}>✕</button>
      </div>

      <div class="space-y-3 my-4 max-h-[60vh] overflow-y-auto pr-1">
        {#if loadingApplicants}
          <div class="text-center py-12 space-y-2">
            <span class="inline-block w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
            <p class="text-xs text-slate-500 dark:text-slate-400 text-dark-sub">Memuat berkas pelamar...</p>
          </div>
        {:else if selectedJobApplicants.length === 0}
          <div class="text-center py-10 space-y-2">
            <p class="text-2xl">⏳</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 text-dark-fix">Belum Ada Pelamar</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-dark-sub">Tugas Anda masih tampil di feed utama freelancer.</p>
          </div>
        {:else}
          {#each selectedJobApplicants as applicant}
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-2xl flex items-center justify-between gap-3 applicant-row">
              <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-bold text-sm flex items-center justify-center overflow-hidden flex-shrink-0 shadow-sm">
                  {#if applicant.avatar}
                    <img src={applicant.avatar} alt="Avatar" class="w-full h-full object-cover" />
                  {:else}
                    {applicant.name.charAt(0)}
                  {/if}
                </div>
                <div>
                  <h4 class="font-bold text-xs text-slate-900 dark:text-white text-dark-fix">{applicant.name}</h4>
                  <div class="flex items-center gap-2 text-[10px] font-semibold mt-0.5">
                    <span class="text-amber-500">⭐ {applicant.rating}</span>
                    <span class="text-slate-400 dark:text-slate-500 text-dark-sub">•</span>
                    <span class="text-purple-600 dark:text-purple-400 font-bold">{applicant.rank}</span>
                  </div>
                </div>
              </div>

              <button 
                onclick={() => openFreelancerProfile(applicant)}
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex-shrink-0"
              >
                Lihat Profil
              </button>
            </div>
          {/each}
        {/if}
      </div>
    </div>
  </div>
{/if}

<!-- Modal 2: Detail Profil & Aksi (Terima/Tolak) -->
{#if isProfileModalOpen && selectedFreelancer}
  <div class="modal-backdrop z-50" onclick={() => isProfileModalOpen = false}>
    <div class="modal-card max-w-md" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center divider-border">
        <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Pratinjau Profil Freelancer</h2>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition" onclick={() => isProfileModalOpen = false}>✕</button>
      </div>

      <div class="space-y-4 pt-4 text-xs">
        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-slate-200 dark:border-slate-800 applicant-card-header">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-black text-lg flex items-center justify-center overflow-hidden shadow-md flex-shrink-0">
            {#if selectedFreelancer.avatar}
              <img src={selectedFreelancer.avatar} alt="Avatar" class="w-full h-full object-cover" />
            {:else}
              {selectedFreelancer.name.charAt(0)}
            {/if}
          </div>
          <div class="space-y-1">
            <h3 class="font-black text-sm text-slate-900 dark:text-white text-dark-fix">{selectedFreelancer.name}</h3>
            <span class="inline-block px-2.5 py-0.5 bg-purple-500/10 text-purple-600 dark:text-purple-400 font-black rounded-md text-[10px] border border-purple-500/20">
              🏆 {selectedFreelancer.rank}
            </span>
            <p class="text-slate-500 dark:text-slate-400 text-dark-sub text-[11px]">
              ⭐ <strong class="text-slate-900 dark:text-white text-dark-fix">{selectedFreelancer.rating}</strong> ({selectedFreelancer.completed_tasks} Pekerjaan Selesai)
            </p>
          </div>
        </div>

        <div class="bg-slate-50 dark:bg-slate-950/50 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2 detail-box">
          <p class="font-bold text-slate-700 dark:text-slate-300 text-dark-sub">Keahlian Utama:</p>
          <div class="flex flex-wrap gap-1.5">
            {#each selectedFreelancer.skills as skill}
              <span class="px-2.5 py-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-bold rounded-lg skill-tag">
                {skill}
              </span>
            {/each}
          </div>
        </div>

        <div class="space-y-1.5">
          <p class="font-bold text-slate-700 dark:text-slate-300 text-dark-sub">Pesan Pelamar (Cover Letter):</p>
          <p class="p-3.5 bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 italic leading-relaxed cover-box">
            "{selectedFreelancer.cover_letter}"
          </p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 divider-border mt-4">
          <button 
            onclick={() => rejectFreelancer(selectedFreelancer)} 
            class="w-full sm:w-auto px-4 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold rounded-xl transition"
          >
            Tolak Pelamar
          </button>
          
          <div class="flex w-full sm:w-auto gap-2.5">
            <button 
              onclick={() => isProfileModalOpen = false} 
              class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 dark:bg-slate-800 font-bold rounded-xl text-slate-700 dark:text-slate-300 transition"
            >
              Tutup
            </button>
            <button 
              onclick={() => acceptFreelancer(selectedFreelancer)} 
              class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition"
            >
              Pilih & Tugaskan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}

<style>
  .modal-backdrop {
    background: rgba(15, 23, 42, 0.6) !important;
    backdrop-filter: blur(4px);
    position: fixed;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    z-index: 100;
  }

  .modal-card {
    border-radius: 20px;
    width: 100%;
    max-width: 520px;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  }

  :global(body.dark-theme) .modal-card {
    background: #0f172a !important;
    border: 1px solid #1e293b !important;
  }

  :global(body:not(.dark-theme)) .hire-jobs-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-title,
  :global(body:not(.dark-theme)) .job-title,
  :global(body:not(.dark-theme)) .text-dark-fix {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .page-sub,
  :global(body:not(.dark-theme)) .text-dark-sub {
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .metric-card,
  :global(body:not(.dark-theme)) .job-item-card,
  :global(body:not(.dark-theme)) .empty-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  }

  :global(body:not(.dark-theme)) .modal-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .applicant-row,
  :global(body:not(.dark-theme)) .applicant-card-header,
  :global(body:not(.dark-theme)) .detail-box,
  :global(body:not(.dark-theme)) .cover-box {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #334155 !important;
  }
</style>