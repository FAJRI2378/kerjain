<script>
  import { onMount } from 'svelte';
  import { api, getBlobUrl } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { waLink } from '$lib/whatsapp.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  // --- STATE UTAMA ---
  let jobs = $state([]);
  let categories = $state([]);
  let loading = $state(true);

  // --- STATE MODAL DETAIL JOB ---
  let detailJob = $state(null);
  let isDetailModalOpen = $state(false);
  let detailProofBlob = $state(null);
  let isActionProcessing = $state(false);

  // --- STATE MODAL REVISI ---
  let isRevisionModalOpen = $state(false);
  let revisionNote = $state('');

  // --- STATE MODAL PELAMAR ---
  let applicantsModal = $state(null);
  let isApplicantsModalOpen = $state(false);
  let loadingApplicants = $state(false);
  let activeJobId = $state(null);
  let processingApplicantId = $state(null);

  // --- STATE MODAL PROFIL FREELANCER ---
  let selectedApplicant = $state(null);
  let isProfileModalOpen = $state(false);

  // --- STATE MODAL EDIT JOB ---
  let isEditModalOpen = $state(false);
  let editJobId = $state(null);
  let editTitle = $state('');
  let editCategory = $state('');
  let editBudget = $state('');
  let editDescription = $state('');
  let editLocation = $state('');
  let editDeadline = $state('');
  let isSubmittingEdit = $state(false);

  // --- LOAD DATA NYATA DARI BACKEND ---
  async function loadJobs() {
    loading = true;
    try {
      const res = await api.get('/api/hire/jobs');
      jobs = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat data tugas.'), 'error');
    } finally {
      loading = false;
    }
  }

  async function loadCategories() {
    try {
      const res = await api.get('/api/categories');
      categories = res.data ?? [];
    } catch (err) {
      categories = [];
    }
  }

  onMount(() => {
    loadCategories();
    loadJobs();
  });

  function statusInfo(status) {
    switch (status) {
      case 'pending':
        return { label: '⏳ Menunggu Review Admin', cls: 'amber', badge: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' };
      case 'approved':
        return { label: '✅ Aktif / Disetujui', cls: 'emerald', badge: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' };
      case 'rejected':
        return { label: '❌ Ditolak Admin', cls: 'red', badge: 'bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20' };
      case 'in_progress':
        return { label: '🚧 Sedang Dikerjakan', cls: 'blue', badge: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20' };
      case 'reviewing':
        return { label: '🔍 Menunggu Review Bukti', cls: 'amber', badge: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' };
      case 'completed':
        return { label: '✅ Selesai', cls: 'emerald', badge: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' };
      case 'cancelled':
        return { label: '🚫 Dibatalkan', cls: 'slate', badge: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' };
      default:
        return { label: status, cls: 'slate', badge: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20' };
    }
  }

  // --- MODAL DETAIL JOB ---
  async function viewDetail(job) {
    try {
      const res = await api.get(`/api/tasks/${job.id}`);
      detailJob = res.data ?? null;
      isDetailModalOpen = true;
      await loadDetailProof();
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat detail tugas.'), 'error');
    }
  }

  async function loadDetailProof() {
    detailProofBlob = null;
    if (!detailJob?.proof_image_url) return;
    try {
      detailProofBlob = await getBlobUrl(detailJob.proof_image_url);
    } catch {
      detailProofBlob = null;
    }
  }

  // --- UNDUH INVOICE PDF ---
  async function downloadInvoice(task) {
    const invoiceId = task.invoice?.id;
    if (!invoiceId) {
      toast('Invoice belum tersedia untuk tugas ini.', 'error');
      return;
    }
    try {
      const url = await getBlobUrl(`/api/invoices/${invoiceId}/pdf`);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${task.invoice?.number ?? `INV-${task.id}`}.pdf`;
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 1500);
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengunduh invoice.'), 'error');
    }
  }

  // --- LOGIKA MODAL PELAMAR ---
  async function viewApplicants(job) {
    activeJobId = job.id;
    isApplicantsModalOpen = true;
    loadingApplicants = true;
    applicantsModal = { jobTitle: job.title, applicants: [] };
    try {
      const res = await api.get(`/api/hire/jobs/${job.id}/applicants`);
      applicantsModal = {
        jobTitle: res.data?.job?.title ?? job.title,
        applicants: res.data?.applicants ?? []
      };
    } catch (err) {
      applicantsModal = { jobTitle: job.title, applicants: [] };
      toast(errorMessage(err, 'Gagal memuat pelamar.'), 'error');
    } finally {
      loadingApplicants = false;
    }
  }

  function openFreelancerProfile(applicant) {
    selectedApplicant = applicant;
    isProfileModalOpen = true;
  }

  async function acceptApplicant(applicant) {
    processingApplicantId = applicant.id;
    try {
      await api.post(`/api/hire/applicants/${applicant.id}/accept`, {});
      toast(`Berhasil memilih ${applicant.name}! Tugas resmi dimulai.`, 'success');
      isProfileModalOpen = false;
      isApplicantsModalOpen = false;
      await loadJobs();
    } catch (err) {
      toast(errorMessage(err, 'Gagal memilih pelamar.'), 'error');
    } finally {
      processingApplicantId = null;
    }
  }

  async function rejectApplicant(applicant) {
    processingApplicantId = applicant.id;
    try {
      await api.post(`/api/hire/applicants/${applicant.id}/reject`, {});
      toast(`Pelamar ${applicant.name} telah ditolak.`, 'info');
      if (applicantsModal) {
        applicantsModal = {
          ...applicantsModal,
          applicants: applicantsModal.applicants.filter((a) => a.id !== applicant.id)
        };
      }
      isProfileModalOpen = false;
      await loadJobs();
    } catch (err) {
      toast(errorMessage(err, 'Gagal menolak pelamar.'), 'error');
    } finally {
      processingApplicantId = null;
    }
  }

  // --- EDIT JOB ---
  function openEditModal(job) {
    editJobId = job.id;
    editTitle = job.title;
    editCategory = job.category?.slug ?? '';
    editBudget = String(job.budget ?? '');
    editDescription = job.description ?? '';
    editLocation = job.location ?? '';
    editDeadline = job.deadline ?? '';
    isEditModalOpen = true;
  }

  async function submitEditJob(e) {
    e.preventDefault();
    isSubmittingEdit = true;
    try {
      await api.put(`/api/hire/jobs/${editJobId}`, {
        title: editTitle,
        category: editCategory,
        budget: Number(editBudget),
        description: editDescription,
        location: editLocation,
        deadline: editDeadline || null
      });
      toast('Tugas berhasil diperbarui dan diajukan ulang!', 'success');
      isEditModalOpen = false;
      await loadJobs();
    } catch (err) {
      toast(errorMessage(err, 'Gagal memperbarui tugas.'), 'error');
    } finally {
      isSubmittingEdit = false;
    }
  }

  // --- HAPUS JOB ---
  async function deleteJob(jobId) {
    if (!confirm('Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.')) return;
    try {
      await api.delete(`/api/hire/jobs/${jobId}`);
      toast('Tugas berhasil dihapus.', 'success');
      await loadJobs();
    } catch (err) {
      toast(errorMessage(err, 'Gagal menghapus tugas.'), 'error');
    }
  }

  // --- SELESAIKAN / REVISI (SAAT REVIEWING) ---
  async function completeJob(job) {
    if (!confirm('Pastikan pekerjaan sudah benar-benar selesai dan Anda puas dengan hasilnya. Dana escrow akan dibayar ke freelancer.')) return;
    isActionProcessing = true;
    try {
      const res = await api.post(`/api/tasks/${job.id}/complete`, {});
      const updated = res.data ?? res;
      detailJob = updated;
      await loadDetailProof();
      await loadJobs();
      toast('Pekerjaan ditandai selesai!', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyelesaikan tugas.'), 'error');
    } finally {
      isActionProcessing = false;
    }
  }

  function openRevisionModal() {
    revisionNote = '';
    isRevisionModalOpen = true;
  }

  async function submitRevision(e) {
    e.preventDefault();
    if (!detailJob) return;
    isActionProcessing = true;
    try {
      const res = await api.post(`/api/tasks/${detailJob.id}/revision`, { note: revisionNote.trim() || null });
      const updated = res.data ?? res;
      detailJob = updated;
      await loadDetailProof();
      await loadJobs();
      isRevisionModalOpen = false;
      toast('Permintaan revisi dikirim ke freelancer.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengirim permintaan revisi.'), 'error');
    } finally {
      isActionProcessing = false;
    }
  }

  const totalApplicantsAll = $derived(jobs.reduce((sum, j) => sum + (j.applicants_count || 0), 0));
</script>

<div class="px-4 sm:px-6 md:px-10 py-6 md:py-10 space-y-6 md:space-y-8 font-sans hire-jobs-page max-w-7xl mx-auto w-full overflow-x-hidden">
  
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6 header-box">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Workspace UMKM</span>
      </div>
      <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight page-title text-dark-fix">Kelola Jobs & Tugas</h1>
      <p class="text-xs md:text-sm text-slate-600 dark:text-slate-400 page-sub text-dark-sub mt-0.5">Pantau tugas yang diposting, tinjau pelamar, dan pilih freelancer terbaik.</p>
    </div>
    
    <a href="/hire/jobs/create" class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2 flex-shrink-0">
      <span>➕</span> Buat Tugas Baru
    </a>
  </div>

  <!-- Metric Summary -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 md:gap-4">
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Total Tugas</p>
        <p class="text-xl md:text-2xl font-black text-slate-900 dark:text-white text-dark-fix">{jobs.length}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold flex-shrink-0">📋</div>
    </div>

    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Total Pelamar Masuk</p>
        <p class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-400">{totalApplicantsAll}</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-lg font-bold flex-shrink-0">👥</div>
    </div>

    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between metric-card">
      <div>
        <p class="text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider text-dark-sub">Status Postingan</p>
        <p class="text-xl md:text-2xl font-black text-slate-900 dark:text-white text-dark-fix">Kelola</p>
      </div>
      <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold flex-shrink-0">⚡</div>
    </div>
  </div>

  <!-- Daftar Job Utama -->
  <div class="space-y-4">
    <div class="flex items-center justify-between px-1">
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
      <div class="space-y-3">
        {#each jobs as job (job.id)}
          <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition duration-200 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 job-item-card">
            
            <div class="space-y-2 flex-1 w-full min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
                  {job.category?.name ?? 'Umum'}
                </span>
                <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400 text-dark-sub">ID: #{job.id}</span>
                {#if job.created_at}
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 text-dark-sub">• {new Date(job.created_at).toLocaleDateString('id-ID')}</span>
                {/if}

                <!-- Indikator Status Job -->
                <span class="px-2 py-0.5 font-black text-[10px] rounded {statusInfo(job.status).badge}">
                  {statusInfo(job.status).label}
                </span>
              </div>

              <h3 class="font-black text-base text-slate-900 dark:text-white job-title text-dark-fix leading-snug break-words">{job.title}</h3>
              
              <div class="flex items-center gap-4 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                <span>Budget Escrow: {formatRupiah(job.budget)}</span>
              </div>

              <!-- Kotak Alasan Penolakan oleh Admin -->
              {#if job.status === 'rejected' && job.rejection_reason}
                <div class="mt-3 p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 text-xs text-red-700 dark:text-red-300 space-y-1.5">
                  <p class="font-bold flex items-center gap-1.5">
                    <span>⚠️</span> Alasan Tugas Ditolak oleh Admin:
                  </p>
                  <p class="italic text-red-600 dark:text-red-400 leading-relaxed break-words">
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

            <!-- Tombol Aksi (Detail, Pelamar, Edit, Hapus) -->
            <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto justify-start lg:justify-end pt-3 lg:pt-0 border-t lg:border-0 border-slate-100 dark:border-slate-800 divider-border">
              <!-- Tombol Lihat Detail -->
              <button 
                onclick={() => viewDetail(job)}
                class="px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5"
                title="Lihat Detail Tugas"
              >
                <span>👁</span> Detail
              </button>

              {#if !['rejected', 'cancelled', 'completed'].includes(job.status)}
                <button 
                  onclick={() => viewApplicants(job)}
                  class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-200 text-white dark:text-slate-900 text-xs font-bold rounded-xl transition flex items-center gap-2 shadow-sm btn-applicants"
                >
                  <span>👥</span>
                  <span>{job.applicants_count ?? 0} Pelamar</span>
                </button>
              {/if}

              {#if !['in_progress', 'reviewing', 'completed'].includes(job.status)}
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
              {/if}
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>

<!-- Modal Detail Job -->
{#if isDetailModalOpen && detailJob}
  <div class="modal-backdrop" onclick={() => isDetailModalOpen = false}>
    <div class="modal-card max-w-lg w-[95%] sm:w-full" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-start gap-4 divider-border">
        <div class="flex items-start gap-2 flex-wrap">
          <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Detail Tugas</h2>
          <span class="px-2.5 py-0.5 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-extrabold text-[11px] rounded-md border border-emerald-500/20">
            {detailJob.category?.name ?? 'Umum'}
          </span>
        </div>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition flex-shrink-0" onclick={() => isDetailModalOpen = false}>✕</button>
      </div>

      <div class="space-y-4 pt-4 text-xs">
        <div class="space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Judul</p>
          <p class="font-black text-sm text-slate-900 dark:text-white text-dark-fix leading-snug break-words">{detailJob.title}</p>
        </div>

        <div class="space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Deskripsi</p>
          <p class="text-slate-600 dark:text-slate-300 text-dark-sub leading-relaxed whitespace-pre-line break-words">{detailJob.description || '-'}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Budget</p>
            <p class="font-black text-emerald-600 dark:text-emerald-400">{formatRupiah(detailJob.budget)}</p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Lokasi</p>
            <p class="font-bold text-slate-700 dark:text-slate-200 break-words">{detailJob.location || '-'}</p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Deadline</p>
            <p class="font-bold text-slate-700 dark:text-slate-200">{detailJob.deadline || '-'}</p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Status</p>
            <span class="px-2 py-0.5 inline-block font-black text-[10px] rounded {statusInfo(detailJob.status).badge}">{statusInfo(detailJob.status).label}</span>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Pelamar</p>
            <p class="font-bold text-slate-700 dark:text-slate-200">{detailJob.applicants_count ?? 0} orang</p>
          </div>
          <div class="space-y-1">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">Dibuat</p>
            <p class="font-bold text-slate-700 dark:text-slate-200">
              {detailJob.created_at ? new Date(detailJob.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-'}
            </p>
          </div>
        </div>

        {#if detailJob.status === 'rejected' && detailJob.rejection_reason}
          <div class="p-3 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/50 text-xs text-red-700 dark:text-red-300">
            <p class="font-bold mb-1">⚠️ Alasan Penolakan:</p>
            <p class="italic leading-relaxed break-words">"{detailJob.rejection_reason}"</p>
          </div>
        {/if}

        {#if detailJob.status === 'reviewing'}
          <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 space-y-2.5 detail-box">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-dark-sub">📎 Bukti Pekerjaan</p>
            {#if detailJob.proof_url}
              <a href={detailJob.proof_url} target="_blank" rel="noreferrer" class="inline-block px-3 py-2 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-xs rounded-lg transition break-all">
                🔗 Lihat Bukti (Link)
              </a>
            {/if}
            {#if detailProofBlob}
              <img src={detailProofBlob} alt="Bukti pekerjaan" class="w-full rounded-lg border border-slate-200 dark:border-slate-700 max-h-72 object-cover" />
            {/if}
            {#if !detailJob.proof_url && !detailProofBlob}
              <p class="text-xs text-slate-500 dark:text-slate-400 text-dark-sub">Bukti belum ditemukan.</p>
            {/if}
          </div>
        {/if}

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
          <button onclick={() => isDetailModalOpen = false} class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition">
            Tutup
          </button>
          {#if detailJob.status === 'reviewing'}
            <button onclick={() => openRevisionModal()} disabled={isActionProcessing} class="w-full sm:w-auto px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition disabled:opacity-50">
              🔁 Minta Revisi
            </button>
            <button onclick={() => completeJob(detailJob)} disabled={isActionProcessing} class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition disabled:opacity-50">
              ✅ Selesaikan & Bayar
            </button>
          {:else if detailJob.status === 'completed' && detailJob.invoice}
            <button onclick={() => downloadInvoice(detailJob)} class="w-full sm:w-auto px-4 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold rounded-xl transition">
              📥 Unduh Invoice PDF
            </button>
          {:else if !['in_progress', 'reviewing', 'completed'].includes(detailJob.status)}
            <button onclick={() => { isDetailModalOpen = false; openEditModal(detailJob); }} class="w-full sm:w-auto px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition">
              ✏️ Edit Tugas
            </button>
          {/if}
        </div>
      </div>
    </div>
  </div>
{/if}

<!-- Modal Daftar Pelamar -->
{#if isApplicantsModalOpen}
  <div class="modal-backdrop" onclick={() => isApplicantsModalOpen = false}>
    <div class="modal-card max-w-lg w-[95%] sm:w-full" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-start divider-border gap-4">
        <div class="min-w-0">
          <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Daftar Pelamar Tugas</h2>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate text-dark-sub">{applicantsModal?.jobTitle}</p>
        </div>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition flex-shrink-0" onclick={() => isApplicantsModalOpen = false}>✕</button>
      </div>

      <div class="space-y-3 my-4 max-h-[60vh] overflow-y-auto pr-1">
        {#if loadingApplicants}
          <div class="text-center py-12 space-y-2">
            <span class="inline-block w-6 h-6 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
            <p class="text-xs text-slate-500 dark:text-slate-400 text-dark-sub">Memuat berkas pelamar...</p>
          </div>
        {:else if !applicantsModal || applicantsModal.applicants.length === 0}
          <div class="text-center py-10 space-y-2">
            <p class="text-2xl">⏳</p>
            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 text-dark-fix">Belum Ada Pelamar</p>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 text-dark-sub">Tugas Anda masih tampil di feed utama freelancer.</p>
          </div>
        {:else}
          {#each applicantsModal.applicants as applicant}
            <div class="p-3.5 bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 applicant-row">
              <div class="flex items-center gap-3 min-w-0 w-full sm:w-auto">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
                  {applicant.name?.charAt(0) ?? '?'}
                </div>
                <div class="min-w-0 flex-1">
                  <h4 class="font-bold text-xs text-slate-900 dark:text-white text-dark-fix truncate">{applicant.name ?? '-'}</h4>
                  <div class="flex items-center gap-2 text-[10px] font-semibold mt-0.5 flex-wrap">
                    <span class="text-amber-500">⭐ {applicant.rating ?? '0.0'}</span>
                    <span class="text-slate-400 dark:text-slate-500 text-dark-sub">•</span>
                    <span class="text-purple-600 dark:text-purple-400 font-bold">{applicant.level ?? 'Worker'}</span>
                    {#if applicant.completed_tasks != null}
                      <span class="text-slate-400 text-dark-sub">• {applicant.completed_tasks} selesai</span>
                    {/if}
                  </div>
                  {#if applicant.status === 'accepted'}
                    <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-black rounded-md text-[10px] border border-emerald-500/20">✓ Diterima</span>
                  {/if}
                </div>
              </div>

              <button 
                onclick={() => openFreelancerProfile(applicant)}
                disabled={applicant.status !== 'pending'}
                class="w-full sm:w-auto px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex-shrink-0 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                {applicant.status === 'pending' ? 'Lihat Profil' : 'Diproses'}
              </button>
            </div>
          {/each}
        {/if}
      </div>
    </div>
  </div>
{/if}

<!-- Modal Detail Profil & Aksi (Terima/Tolak) -->
{#if isProfileModalOpen && selectedApplicant}
  <div class="modal-backdrop z-50" onclick={() => isProfileModalOpen = false}>
    <div class="modal-card max-w-md w-[95%] sm:w-full" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center divider-border">
        <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Pratinjau Profil Freelancer</h2>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition flex-shrink-0" onclick={() => isProfileModalOpen = false}>✕</button>
      </div>

      <div class="space-y-4 pt-4 text-xs">
        <div class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-slate-200 dark:border-slate-800 applicant-card-header">
          <div class="w-14 h-14 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-600 text-white font-black text-lg flex items-center justify-center shadow-md flex-shrink-0">
            {selectedApplicant.name?.charAt(0) ?? '?'}
          </div>
          <div class="space-y-1 min-w-0 flex-1">
            <h3 class="font-black text-sm text-slate-900 dark:text-white text-dark-fix truncate">{selectedApplicant.name ?? '-'}</h3>
            <span class="inline-block px-2.5 py-0.5 bg-purple-500/10 text-purple-600 dark:text-purple-400 font-black rounded-md text-[10px] border border-purple-500/20">
              🏆 {selectedApplicant.level ?? 'Worker'}
            </span>
            <p class="text-slate-500 dark:text-slate-400 text-dark-sub text-[11px] truncate">
              ⭐ <strong class="text-slate-900 dark:text-white text-dark-fix">{selectedApplicant.rating ?? '0.0'}</strong> ({selectedApplicant.completed_tasks ?? 0} Pekerjaan Selesai)
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-2.5 p-3.5 bg-white dark:bg-slate-950/50 rounded-2xl border border-slate-200 dark:border-slate-800 contact-box">
          <div class="flex items-center gap-2.5 text-xs">
            <span class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">✉️</span>
            <div class="min-w-0 flex-1">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Email</p>
              <p class="font-bold text-slate-800 dark:text-slate-100 truncate">{selectedApplicant.email || 'Belum diisi'}</p>
            </div>
          </div>
          <div class="flex items-center gap-2.5 text-xs">
            <span class="w-7 h-7 rounded-lg bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center flex-shrink-0">📞</span>
            <div class="min-w-0 flex-1">
              <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">No. Handphone</p>
              <p class="font-bold text-slate-800 dark:text-slate-100 truncate">{selectedApplicant.phone || 'Belum diisi'}</p>
            </div>
          </div>

          {#if waLink(selectedApplicant.phone)}
            <a 
              href={waLink(selectedApplicant.phone, `Halo ${selectedApplicant.name}, kami melihat lamaran Anda di Kerjain untuk lowongan kami.`)}
              target="_blank" rel="noreferrer"
              class="flex items-center justify-center gap-2 px-4 py-2.5 bg-[#25d366] hover:bg-[#1eb858] text-white font-bold rounded-xl transition w-full"
            >
              💬 Hubungi via WhatsApp
            </a>
          {/if}
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 divider-border mt-4">
          <button 
            onclick={() => rejectApplicant(selectedApplicant)} 
            disabled={processingApplicantId === selectedApplicant.id}
            class="w-full sm:w-auto px-4 py-2.5 bg-red-50 hover:bg-red-100 dark:bg-red-500/10 text-red-600 dark:text-red-400 font-bold rounded-xl transition disabled:opacity-50"
          >
            Tolak Pelamar
          </button>
          
          <div class="flex flex-col sm:flex-row w-full sm:w-auto gap-2.5">
            <button 
              onclick={() => isProfileModalOpen = false} 
              class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 dark:bg-slate-800 font-bold rounded-xl text-slate-700 dark:text-slate-300 transition"
            >
              Tutup
            </button>
            <button 
              onclick={() => acceptApplicant(selectedApplicant)} 
              disabled={processingApplicantId === selectedApplicant.id}
              class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 transition disabled:opacity-50"
            >
              {processingApplicantId === selectedApplicant.id ? 'Memproses...' : 'Pilih & Tugaskan'}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
{/if}

<!-- Modal Edit Job -->
{#if isEditModalOpen}
  <div class="modal-backdrop" onclick={() => isEditModalOpen = false}>
    <div class="modal-card max-w-lg w-[95%] sm:w-full" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center divider-border">
        <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">Edit & Perbarui Tugas</h2>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition flex-shrink-0" onclick={() => isEditModalOpen = false}>✕</button>
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
          <select 
            id="edit-cat"
            bind:value={editCategory}
            required
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
          >
            <option value="" disabled>Pilih kategori</option>
            {#each categories as cat}
              <option value={cat.slug}>{cat.name}</option>
            {/each}
          </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1.5">
            <label for="edit-budget" class="font-bold text-slate-700 dark:text-slate-300">Budget / Fee (Rp)</label>
            <input 
              id="edit-budget"
              type="number" 
              bind:value={editBudget} 
              min="50000"
              required 
              class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
            />
          </div>
          <div class="space-y-1.5">
            <label for="edit-location" class="font-bold text-slate-700 dark:text-slate-300">Lokasi</label>
            <input 
              id="edit-location"
              type="text" 
              bind:value={editLocation} 
              required 
              class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label for="edit-desc" class="font-bold text-slate-700 dark:text-slate-300">Deskripsi Tugas</label>
          <textarea 
            id="edit-desc"
            bind:value={editDescription} 
            rows="3" 
            required 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 resize-none"
          ></textarea>
        </div>

        <div class="space-y-1.5">
          <label for="edit-deadline" class="font-bold text-slate-700 dark:text-slate-300">Deadline (opsional)</label>
          <input 
            id="edit-deadline"
            type="text" 
            bind:value={editDeadline} 
            placeholder="Contoh: 30 Sep 2026" 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500"
          />
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
          <button 
            type="button" 
            onclick={() => isEditModalOpen = false} 
            class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl"
          >
            Batal
          </button>
          <button 
            type="submit" 
            disabled={isSubmittingEdit}
            class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 disabled:opacity-50"
          >
            {isSubmittingEdit ? 'Menyimpan...' : 'Simpan Perubahan'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<!-- Modal Minta Revisi -->
{#if isRevisionModalOpen && detailJob}
  <div class="modal-backdrop z-50" onclick={() => isRevisionModalOpen = false}>
    <div class="modal-card max-w-md w-[95%] sm:w-full" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header border-b border-slate-200 dark:border-slate-800 pb-4 flex justify-between items-center divider-border">
        <h2 class="font-black text-base text-slate-900 dark:text-white text-dark-fix">🔁 Minta Revisi Pekerjaan</h2>
        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold flex items-center justify-center transition flex-shrink-0" onclick={() => isRevisionModalOpen = false}>✕</button>
      </div>

      <form onsubmit={submitRevision} class="space-y-4 pt-4 text-xs">
        <div class="space-y-1.5">
          <label for="revision-note" class="font-bold text-slate-700 dark:text-slate-300">Catatan Revisi (opsional)</label>
          <textarea 
            id="revision-note"
            bind:value={revisionNote} 
            rows="3" 
            class="w-full p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 resize-none"
            placeholder="Contoh: foto bukti kurang jelas, mohon foto ulang dari dekat."
          ></textarea>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 text-dark-sub">Pekerjaan akan kembali ke tahap "Dalam Pengerjaan" agar freelancer dapat memperbaiki dan mengirim ulang bukti.</p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
          <button 
            type="button" 
            onclick={() => isRevisionModalOpen = false} 
            class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl"
          >
            Batal
          </button>
          <button 
            type="submit" 
            disabled={isActionProcessing}
            class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-lg shadow-amber-500/20 disabled:opacity-50"
          >
            {isActionProcessing ? 'Mengirim...' : 'Kirim Permintaan Revisi'}
          </button>
        </div>
      </form>
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
    padding: 20px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-height: 90vh;
    overflow-y: auto;
  }

  @media (min-width: 640px) {
    .modal-card {
      padding: 24px;
    }
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

  :global(body:not(.dark-theme)) .contact-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }
</style>