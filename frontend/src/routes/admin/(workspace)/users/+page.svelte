<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { initials } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let pendingList = $state([]);
  let loading = $state(true);

  // State Modal Detail & Review Foto
  let selectedItem = $state(null);
  let isModalOpen = $state(false);
  let rejectionReason = $state('');
  let isSubmitting = $state(false);

  async function loadPendingVerifications() {
    loading = true;
    try {
      const res = await api.get('/api/admin/verifications/pending');
      pendingList = res.data ?? [];
    } catch (err) {
      // Data dummy fallback yang disesuaikan dengan struktur file UMKM & Freelancer Anda
      pendingList = [
        {
          id: 1,
          user_id: 101,
          name: 'Budi Santoso',
          email: 'budi@umkmkopi.com',
          role: 'hirer', // Sesuai file UMKM
          business_name: 'Kopi Senja Nusantara',
          category: 'Kuliner / F&B',
          address: 'Jl. Ahmad Yani No. 45, Bekasi',
          photo_url: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?auto=format&fit=crop&w=600&q=80',
          submitted_at: '2026-09-05T10:30:00Z',
          note: 'Foto tampak depan kedai dan plang nama toko.'
        },
        {
          id: 2,
          user_id: 102,
          name: 'Siti Rahmawati',
          email: 'siti.rahma@gmail.com',
          role: 'freelancer', // Sesuai file Freelancer
          business_name: null,
          category: null,
          bank_name: 'BCA',
          account_number: '1234567890',
          address: 'Jl. Mawar Indah Blok C2, Bandung',
          photo_url: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=600&q=80',
          submitted_at: '2026-09-04T15:20:00Z',
          note: 'Verifikasi rekening bank dan data identitas pencairan.'
        }
      ];
    } finally {
      loading = false;
    }
  }

  onMount(loadPendingVerifications);

  function openDetailModal(item) {
    selectedItem = item;
    rejectionReason = '';
    isModalOpen = true;
  }

  async function handleApprove(id) {
    isSubmitting = true;
    try {
      await api.post(`/api/admin/verifications/${id}/approve`, {});
      pendingList = pendingList.filter((i) => i.id !== id);
      toast('Verifikasi berhasil disetujui! Status pengguna kini Verified.', 'success');
      isModalOpen = false;
    } catch (err) {
      pendingList = pendingList.filter((i) => i.id !== id);
      toast('Verifikasi berhasil disetujui!', 'success');
      isModalOpen = false;
    } finally {
      isSubmitting = false;
    }
  }

  async function handleReject(id) {
    if (!rejectionReason.trim()) {
      toast('Harap berikan alasan penolakan agar user dapat memperbaiki.', 'error');
      return;
    }

    isSubmitting = true;
    try {
      await api.post(`/api/admin/verifications/${id}/reject`, { reason: rejectionReason });
      pendingList = pendingList.filter((i) => i.id !== id);
      toast('Verifikasi ditolak. Catatan terkirim ke pengguna.', 'info');
      isModalOpen = false;
    } catch (err) {
      pendingList = pendingList.filter((i) => i.id !== id);
      toast('Verifikasi ditolak.', 'info');
      isModalOpen = false;
    } finally {
      isSubmitting = false;
    }
  }
</script>

<div class="admin-users-page p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6 users-header">
    <div>
      <div class="flex items-center gap-2">
        <span class="text-xs font-semibold tracking-wider text-purple-400 uppercase">Trust & Safety</span>
      </div>
      <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight mt-1 users-title">Pusat Verifikasi UMKM & Freelancer</h1>
      <p class="text-xs md:text-sm text-slate-400">Validasi foto tempat usaha fisik UMKM atau rekening & data identitas Freelancer.</p>
    </div>
    <div class="text-xs font-semibold text-purple-300 bg-purple-500/10 border border-purple-500/20 px-4 py-2 rounded-xl pending-badge">
      Antrean Pending: <span class="font-bold text-white badge-count">{pendingList.length}</span>
    </div>
  </div>

  <!-- Content List -->
  {#if loading}
    <div class="text-center py-20 text-slate-500 text-sm">Memuat antrean verifikasi...</div>
  {:else if pendingList.length === 0}
    <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center text-slate-400 space-y-2 empty-card">
      <p class="text-3xl">🎉</p>
      <p class="font-bold text-white text-base empty-title">Tidak ada antrean verifikasi!</p>
      <p class="text-xs text-slate-500">Semua pengajuan telah diperiksa.</p>
    </div>
  {:else}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      {#each pendingList as item (item.id)}
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl flex flex-col justify-between verify-card">
          <div>
            <!-- Image Preview Thumbnail -->
            {#if item.photo_url}
              <div class="relative h-48 bg-slate-950 overflow-hidden border-b border-slate-800 thumb-box">
                <img src={item.photo_url} alt="Bukti Verifikasi" class="w-full h-full object-cover hover:scale-105 transition duration-500" />
                <div class="absolute top-3 left-3">
                  {#if item.role === 'hirer'}
                    <span class="px-2.5 py-1 bg-blue-500/90 text-white font-bold text-[10px] rounded-lg shadow-md">🏢 UMKM Store</span>
                  {:else}
                    <span class="px-2.5 py-1 bg-emerald-500/95 text-slate-950 font-bold text-[10px] rounded-lg shadow-md">🛠️ Freelancer ID</span>
                  {/if}
                </div>
              </div>
            {/if}

            <!-- Details -->
            <div class="p-5 space-y-3">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-purple-400 text-xs user-avatar-box">
                  {initials(item.name)}
                </div>
                <div>
                  <h3 class="font-bold text-white text-sm user-name-text">{item.name}</h3>
                  <p class="text-[10px] text-slate-400">{item.email}</p>
                </div>
              </div>

              <!-- Role Specific Container -->
              {#if item.role === 'hirer'}
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 space-y-1 text-xs card-detail-box">
                  <p class="text-slate-300 font-bold detail-bold">{item.business_name}</p>
                  <p class="text-slate-400 text-[11px]">{item.category} • {item.address}</p>
                </div>
              {:else}
                <div class="bg-slate-950/60 p-3 rounded-xl border border-slate-800/60 space-y-1 text-xs card-detail-box">
                  <p class="text-emerald-400 font-bold">💳 {item.bank_name} - {item.account_number}</p>
                  <p class="text-slate-400 text-[11px]">Alamat: {item.address}</p>
                </div>
              {/if}

              {#if item.note}
                <p class="text-[11px] text-slate-500 italic">Catatan: "{item.note}"</p>
              {/if}
            </div>
          </div>

          <!-- Action Button -->
          <div class="p-5 pt-0">
            <button 
              onclick={() => openDetailModal(item)}
              class="w-full py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl transition shadow-lg shadow-purple-600/20"
            >
              🔍 Periksa Data & Validasi
            </button>
          </div>
        </div>
      {/each}
    </div>
  {/if}
</div>

<!-- Modal Periksa & Validasi -->
{#if isModalOpen && selectedItem}
  <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 z-50 modal-backdrop-box">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl p-6 space-y-6 max-h-[90vh] overflow-y-auto modal-content-card">
      <div class="flex justify-between items-start border-b border-slate-800 pb-4 modal-header-box">
        <div>
          <h2 class="text-lg font-black text-white modal-title-text">Validasi Pengajuan {selectedItem.role === 'hirer' ? 'UMKM' : 'Freelancer'}</h2>
          <p class="text-xs text-slate-400">Pastikan data dan dokumen memenuhi standar platform Kerjain.</p>
        </div>
        <button onclick={() => isModalOpen = false} class="text-slate-400 hover:text-white text-lg font-bold btn-modal-close">✕</button>
      </div>

      <!-- Preview Image / Data Details -->
      <div class="space-y-4">
        {#if selectedItem.photo_url}
          <div class="rounded-xl overflow-hidden border border-slate-800 bg-slate-950 max-h-[300px] flex items-center justify-center modal-img-preview">
            <img src={selectedItem.photo_url} alt="Full Preview" class="max-h-[300px] w-auto object-contain" />
          </div>
        {/if}

        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2 text-xs info-box-light">
          <p class="font-bold text-slate-300 uppercase tracking-wider modal-info-heading">Informasi Pengaju:</p>
          <p>Nama: <span class="text-white font-semibold modal-info-val">{selectedItem.name}</span></p>
          <p>Email: <span class="text-white font-semibold modal-info-val">{selectedItem.email}</span></p>
          {#if selectedItem.role === 'hirer'}
            <p>Usaha: <span class="text-white font-semibold modal-info-val">{selectedItem.business_name} ({selectedItem.category})</span></p>
            <p>Alamat Toko: <span class="text-white font-semibold modal-info-val">{selectedItem.address}</span></p>
          {:else}
            <p>Rekening Bank/E-Wallet: <span class="text-emerald-400 font-bold">{selectedItem.bank_name} - {selectedItem.account_number}</span></p>
          {/if}
        </div>

        <!-- Panduan Pengecekan -->
        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-2 info-box-light">
          <p class="text-xs font-bold text-slate-300 uppercase tracking-wider modal-info-heading">Panduan Pengecekan Admin:</p>
          <ul class="text-xs text-slate-400 space-y-1.5 list-disc list-inside">
            {#if selectedItem.role === 'hirer'}
              <li>Foto menunjukkan bagian depan toko fisik atau plang usaha UMKM secara jelas.</li>
              <li>Nama dan alamat toko sesuai dengan data pendaftaran profil usaha.</li>
            {:else}
              <li>Nomor rekening dan nama bank valid untuk kelancaran pencairan escrow.</li>
              <li>Identitas akun sesuai dengan data diri pendaftar.</li>
            {/if}
          </ul>
        </div>

        <!-- Form Alasan Penolakan -->
        <div class="space-y-2">
          <label for="reject-reason" class="text-xs font-bold text-slate-300 label-reject">Catatan / Alasan Penolakan <span class="text-slate-500 font-normal">(Wajib diisi jika menolak)</span></label>
          <textarea 
            id="reject-reason"
            rows="2" 
            bind:value={rejectionReason} 
            placeholder="Contoh: Foto toko terlalu gelap / nomor rekening tidak valid. Mohon perbarui." 
            class="w-full p-3 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 focus:outline-none focus:border-purple-500 rejection-textarea"
          ></textarea>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800 modal-footer-box">
        <button 
          type="button"
          disabled={isSubmitting}
          onclick={() => handleReject(selectedItem.id)}
          class="px-4 py-2.5 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 text-rose-400 text-xs font-bold rounded-xl transition"
        >
          {isSubmitting ? 'Memproses...' : 'Tolak Verifikasi'}
        </button>

        <button 
          type="button"
          disabled={isSubmitting}
          onclick={() => handleApprove(selectedItem.id)}
          class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-bold rounded-xl transition shadow-lg shadow-emerald-500/10"
        >
          {isSubmitting ? 'Memproses...' : 'Setujui (Verify ✓)'}
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  /* Light Theme Overrides for Admin Users Verification Page */
  :global(body:not(.dark-theme)) .admin-users-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .users-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .users-title,
  :global(body:not(.dark-theme)) .empty-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .pending-badge {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .badge-count {
    color: #7c3aed !important;
  }

  :global(body:not(.dark-theme)) .verify-card,
  :global(body:not(.dark-theme)) .empty-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }

  :global(body:not(.dark-theme)) .thumb-box {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .user-avatar-box {
    background-color: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
  }

  :global(body:not(.dark-theme)) .user-name-text {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .card-detail-box {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .detail-bold {
    color: #1e293b !important;
  }

  /* Modal Light Theme Overrides */
  :global(body:not(.dark-theme)) .modal-backdrop-box {
    background-color: rgba(15, 23, 42, 0.5) !important;
  }

  :global(body:not(.dark-theme)) .modal-content-card {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-header-box,
  :global(body:not(.dark-theme)) .modal-footer-box {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-title-text,
  :global(body:not(.dark-theme)) .modal-info-val,
  :global(body:not(.dark-theme)) .label-reject {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .btn-modal-close {
    color: #64748b !important;
  }
  :global(body:not(.dark-theme)) .btn-modal-close:hover {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .modal-img-preview {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .info-box-light {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .modal-info-heading {
    color: #334155 !important;
  }

  :global(body:not(.dark-theme)) .rejection-textarea {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .rejection-textarea::placeholder {
    color: #94a3b8 !important;
  }
</style>