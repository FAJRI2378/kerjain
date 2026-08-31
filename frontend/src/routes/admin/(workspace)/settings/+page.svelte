<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let platformCommission = $state(10);
  let autoApproveJobs = $state(false);
  let maintenanceMode = $state(false);
  let emailNotifications = $state(true);
  let loading = $state(true);
  let saving = $state(false);

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/admin/settings');
      platformCommission = res.data?.platform_commission ?? 10;
      autoApproveJobs = !!res.data?.auto_approve_jobs;
      maintenanceMode = !!res.data?.maintenance_mode;
      emailNotifications = !!res.data?.email_notifications;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat pengaturan.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  async function handleSaveSettings(e) {
    e.preventDefault();
    saving = true;
    try {
      await api.put('/api/admin/settings', {
        platform_commission: Number(platformCommission),
        auto_approve_jobs: autoApproveJobs,
        maintenance_mode: maintenanceMode,
        email_notifications: emailNotifications
      });
      toast('Pengaturan sistem berhasil disimpan!', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyimpan pengaturan.'), 'error');
    } finally {
      saving = false;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-4xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Pengaturan Sistem</h1>
    <p class="text-xs md:text-sm text-slate-400">Konfigurasi parameter platform, komisi, dan kebijakan aturan bisnis.</p>
  </div>

  <form onsubmit={handleSaveSettings} class="space-y-6">
    {#if loading}
      <p class="text-xs text-slate-500 p-4 bg-slate-900/60 border border-slate-800 rounded-xl">Memuat pengaturan...</p>
    {/if}
    <!-- Section 1: Monetisasi & Komisi -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <h3 class="font-bold text-white text-sm flex items-center gap-2">
        <span>💰</span> Skema Komisi & Keuangan
      </h3>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
          <label for="commission" class="block font-medium text-slate-300 mb-1.5">Potongan Komisi Platform (%)</label>
          <div class="relative">
            <input 
              id="commission"
              type="number" 
              bind:value={platformCommission}
              min="0"
              max="50"
              class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:border-purple-500 transition"
            />
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500">%</span>
          </div>
          <p class="text-[10px] text-slate-500 mt-1">Dipotong otomatis dari total bayaran per tugas selesai.</p>
        </div>
      </div>
    </div>

    <!-- Section 2: Moderasi Automation -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <h3 class="font-bold text-white text-sm flex items-center gap-2">
        <span>⚙️</span> Otomatisasi Moderasi
      </h3>

      <div class="space-y-4 text-xs">
        <!-- Toggle Auto Approve -->
        <div class="flex items-center justify-between p-3 bg-slate-950/60 rounded-xl border border-slate-800/60">
          <div>
            <p class="font-bold text-white">Auto-Approve Postingan Tugas</p>
            <p class="text-[10px] text-slate-400">Jika aktif, tugas yang diposting UMKM langsung tayang tanpa review manual.</p>
          </div>
          <input 
            type="checkbox" 
            bind:checked={autoApproveJobs} 
            class="w-5 h-5 accent-purple-600 rounded cursor-pointer"
          />
        </div>

        <!-- Toggle Email Notification -->
        <div class="flex items-center justify-between p-3 bg-slate-950/60 rounded-xl border border-slate-800/60">
          <div>
            <p class="font-bold text-white">Notifikasi Email Admin</p>
            <p class="text-[10px] text-slate-400">Kirim alert email setiap ada verifikasi KTP/ID pengguna baru.</p>
          </div>
          <input 
            type="checkbox" 
            bind:checked={emailNotifications} 
            class="w-5 h-5 accent-purple-600 rounded cursor-pointer"
          />
        </div>
      </div>
    </div>

    <!-- Section 3: System Status / Maintenance -->
    <div class="p-6 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl space-y-4">
      <h3 class="font-bold text-rose-400 text-sm flex items-center gap-2">
        <span>⚠️</span> Maintenance System
      </h3>

      <div class="flex items-center justify-between p-3 bg-slate-950/60 rounded-xl border border-slate-800/60">
        <div>
          <p class="font-bold text-white">Aktifkan Maintenance Mode</p>
          <p class="text-[10px] text-slate-400">Akses pengguna biasa akan dikunci sementara untuk pemeliharaan sistem.</p>
        </div>
        <input 
          type="checkbox" 
          bind:checked={maintenanceMode} 
          class="w-5 h-5 accent-rose-600 rounded cursor-pointer"
        />
      </div>
    </div>

    <!-- Submit Button -->
    <div class="flex justify-end">
      <button 
        type="submit" 
        disabled={loading || saving}
        class="px-6 py-3 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-lg shadow-purple-600/20 transition"
      >
        {saving ? 'Menyimpan...' : 'Simpan Perubahan'}
      </button>
    </div>
  </form>
</div>