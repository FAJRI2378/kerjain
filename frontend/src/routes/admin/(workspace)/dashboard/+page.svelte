<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah, formatNumber } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let stats = $state({
    totalUsers: 0,
    activeTasks: 0,
    pendingVerifications: 0,
    monthlyRevenue: 0
  });

  let pendingJobs = $state([]);
  let loading = $state(true);
  let actingId = $state(null);

  // State untuk Modal Alasan Penolakan
  let isRejectModalOpen = $state(false);
  let targetJobId = $state(null);
  let rejectionReason = $state('');

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/admin/dashboard');
      const d = res.data;
      stats = {
        totalUsers: d.total_users ?? 0,
        activeTasks: d.active_tasks ?? 0,
        pendingVerifications: d.pending_verifications ?? 0,
        monthlyRevenue: d.monthly_revenue ?? 0
      };
      pendingJobs = d.pending_jobs ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat dashboard.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  async function handleApprove(id) {
    actingId = id;
    try {
      await api.post(`/api/admin/tasks/${id}/approve`, {});
      pendingJobs = pendingJobs.filter((j) => j.id !== id);
      toast('Postingan disetujui.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyetujui.'), 'error');
      await load();
    } finally {
      actingId = null;
    }
  }

  // Membuka modal alasan penolakan
  function openRejectModal(id) {
    targetJobId = id;
    rejectionReason = '';
    isRejectModalOpen = true;
  }

  // Eksekusi penolakan dengan mengirimkan alasan
  async function submitReject(e) {
    e.preventDefault();
    if (!rejectionReason.trim()) {
      toast('Alasan penolakan wajib diisi.', 'error');
      return;
    }

    isRejectModalOpen = false;
    actingId = targetJobId;

    try {
      await api.post(`/api/admin/tasks/${targetJobId}/reject`, {
        reason: rejectionReason
      });
      pendingJobs = pendingJobs.filter((j) => j.id !== targetJobId);
      toast('Postingan ditolak dengan alasan.', 'info');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menolak.'), 'error');
      await load();
    } finally {
      actingId = null;
      targetJobId = null;
    }
  }
</script>

<div class="admin-dashboard-page min-h-screen bg-slate-950 text-slate-100 p-6 md:p-10 space-y-8 font-sans">
  
  <!-- Top Navigation & Header -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800/80 pb-6 dashboard-header">
    <div>
      <div class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 animate-pulse"></span>
        <span class="text-xs font-semibold tracking-wider text-purple-400 uppercase">Admin Portal</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1 control-center-title">Control Center</h1>
      <p class="text-xs md:text-sm text-slate-400 control-center-sub">Monitoring real-time aktivitas platform dan kelola verifikasi sistem.</p>
    </div>

    <div class="flex items-center gap-3">
      <span class="px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold rounded-xl flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        System Healthy
      </span>
      <button class="px-4 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 text-xs font-semibold text-slate-300 rounded-xl transition btn-report">
        Unduh Laporan
      </button>
    </div>
  </div>

  <!-- Key Metrics Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3 metric-card">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider metric-label">Total Pengguna</span>
        <span class="text-lg">👥</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-white metric-val">{formatNumber(stats.totalUsers)}</span>
        <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-md">+12% /bln</span>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3 metric-card">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider metric-label">Tugas Berjalan</span>
        <span class="text-lg">⚡</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-white metric-val">{stats.activeTasks}</span>
        <span class="text-[10px] text-blue-400 font-semibold bg-blue-500/10 px-2 py-0.5 rounded-md">Aktif</span>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3 metric-card">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider metric-label">Antrean Verifikasi</span>
        <span class="text-lg">🪪</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-3xl font-black text-purple-400 metric-val">{stats.pendingVerifications}</span>
        <span class="text-[10px] text-purple-300 font-semibold bg-purple-500/10 px-2 py-0.5 rounded-md">Perlu Review</span>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-3 metric-card">
      <div class="flex justify-between items-center text-slate-400">
        <span class="text-xs font-bold uppercase tracking-wider metric-label">Volume Transaksi</span>
        <span class="text-lg">💰</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-xl font-black text-white metric-val">{formatRupiah(stats.monthlyRevenue)}</span>
        <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-md">+8%</span>
      </div>
    </div>
  </div>

  <!-- Moderation Queue Table Section -->
  <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-6 backdrop-blur-xl space-y-5 moderation-card">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="font-bold text-white text-base moderation-title">Antrean Moderasi Postingan</h3>
        <p class="text-xs text-slate-400 moderation-sub">Review postingan baru dari UMKM sebelum ditayangkan ke publik.</p>
      </div>
      <span class="text-xs font-bold text-slate-400 bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800 w-fit queue-badge">
        Sisa Antrean: <span class="text-purple-400">{pendingJobs.length}</span>
      </span>
    </div>

    {#if loading}
      <div class="text-center text-slate-400 text-sm py-12">Memuat antrean moderasi...</div>
    {:else if pendingJobs.length === 0}
      <div class="py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-xl empty-queue">
        🎉 Semua postingan telah direview! Tidak ada antrean tersisa.
      </div>
    {:else}
      <div class="space-y-3">
        {#each pendingJobs as job (job.id)}
          <div class="flex flex-col md:flex-row md:items-center justify-between p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl hover:border-slate-700 transition gap-4 job-row">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-purple-500/10 border border-purple-500/20 text-purple-400 text-[10px] font-bold rounded">
                  #{job.id}
                </span>
                <span class="text-xs text-slate-500 font-medium category-text">{job.category?.name ?? '-'}</span>
                {#if job.created_at}<span class="text-xs text-slate-600 date-text">• {new Date(job.created_at).toLocaleString('id-ID')}</span>{/if}
              </div>
              <p class="font-bold text-white text-sm job-title-text">{job.title}</p>
              <p class="text-xs text-slate-400 job-owner-wrap">Diposting oleh: <span class="text-slate-200 font-medium job-owner">{job.owner?.name ?? '-'}</span></p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2 self-end md:self-center">
              <button 
                onclick={() => handleApprove(job.id)}
                disabled={actingId === job.id}
                class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition disabled:opacity-50"
              >
                Setujui
              </button>
              <button 
                onclick={() => openRejectModal(job.id)}
                disabled={actingId === job.id}
                class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold text-xs rounded-xl transition disabled:opacity-50"
              >
                Tolak
              </button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </div>

</div>

<!-- Modal Alasan Penolakan -->
{#if isRejectModalOpen}
  <div class="modal-backdrop" onclick={() => isRejectModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="flex justify-between items-center mb-4 border-b border-slate-800 pb-3">
        <h3 class="font-bold text-sm text-white modal-title-text">Alasan Penolakan Tugas</h3>
        <button onclick={() => isRejectModalOpen = false} class="text-slate-400 hover:text-white font-bold">✕</button>
      </div>

      <form onsubmit={submitReject} class="space-y-4">
        <div>
          <label for="admin-reject-reason" class="block text-xs font-semibold text-slate-300 mb-2 modal-label-text">Tuliskan alasan penolakan untuk pemberi kerja:</label>
          <textarea 
            id="admin-reject-reason"
            bind:value={rejectionReason} 
            rows="3" 
            placeholder="Contoh: Deskripsi tugas kurang lengkap atau tidak sesuai aturan..."
            class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition resize-none reject-textarea"
            required
          ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button 
            type="button" 
            onclick={() => isRejectModalOpen = false} 
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl transition modal-btn-cancel"
          >
            Batal
          </button>
          <button 
            type="submit" 
            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition"
          >
            Konfirmasi Tolak
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  /* Modal Backdrop Styling */
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
  }

  .modal-card {
    background-color: #0f172a;
    border: 1px solid #1e293b;
    border-radius: 20px;
    width: 100%;
    max-width: 440px;
    padding: 20px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  }

  /* Light Theme Adjustments for Admin Dashboard */
  :global(body:not(.dark-theme)) .admin-dashboard-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .dashboard-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .control-center-title,
  :global(body:not(.dark-theme)) .moderation-title,
  :global(body:not(.dark-theme)) .job-title-text {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .control-center-sub,
  :global(body:not(.dark-theme)) .moderation-sub {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .metric-card,
  :global(body:not(.dark-theme)) .moderation-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  :global(body:not(.dark-theme)) .metric-label {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .metric-card .metric-val {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .btn-report {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .btn-report:hover {
    background-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .queue-badge {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
    color: #475569 !important;
  }

  :global(body:not(.dark-theme)) .empty-queue {
    border-color: #cbd5e1 !important;
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .job-row {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .category-text {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .date-text {
    color: #94a3b8 !important;
  }

  :global(body:not(.dark-theme)) .job-owner-wrap {
    color: #64748b !important;
  }

  :global(body:not(.dark-theme)) .job-owner {
    color: #334155 !important;
  }

  /* Modal Light Theme Overrides */
  :global(body:not(.dark-theme)) .modal-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-title-text {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .modal-label-text {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .reject-textarea {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .modal-btn-cancel {
    background-color: #f1f5f9 !important;
    color: #334155 !important;
  }
</style>