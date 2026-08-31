<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let businessName = $state('');
  let category = $state('');
  let phone = $state('');
  let loading = $state(true);
  let submitting = $state(false);
  let formErrors = $state({});

  onMount(async () => {
    try {
      const res = await api.get('/api/hire/profile');
      const d = res.data;
      if (d) {
        businessName = d.business_name ?? '';
        category = d.category ?? '';
        phone = d.phone ?? '';
      }
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat profil.'), 'error');
    } finally {
      loading = false;
    }
  });

  async function handleSave(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      await api.put('/api/hire/profile', { business_name: businessName, category, phone });
      toast('Profil usaha berhasil diperbarui!', 'success');
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Gagal menyimpan profil.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-3xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Profil Usaha & Tagihan</h1>
    <p class="text-xs md:text-sm text-slate-400">Atur informasi usaha UMKM dan metode pembayaran untuk pengisian saldo Escrow.</p>
  </div>

  {#if loading}
    <div class="text-center text-slate-400 text-sm py-12">Memuat profil...</div>
  {:else}
  <form onsubmit={handleSave} class="p-6 bg-slate-900/60 border border-slate-800/80 rounded-2xl backdrop-blur-xl space-y-5">
    <div>
      <label for="bname" class="block font-medium text-slate-300 text-xs mb-1.5">Nama Usaha / Bisnis</label>
      <input 
        id="bname"
        type="text" 
        bind:value={businessName}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      {#if formErrors.business_name}<p class="text-red-400 mt-1 text-xs">{(formErrors.business_name).join(', ')}</p>{/if}
    </div>

    <div>
      <label for="bcat" class="block font-medium text-slate-300 text-xs mb-1.5">Kategori Bidang Usaha</label>
      <input 
        id="bcat"
        type="text" 
        bind:value={category}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      {#if formErrors.category}<p class="text-red-400 mt-1 text-xs">{(formErrors.category).join(', ')}</p>{/if}
    </div>

    <div>
      <label for="phone" class="block font-medium text-slate-300 text-xs mb-1.5">Nomor WhatsApp PIC</label>
      <input 
        id="phone"
        type="text" 
        bind:value={phone}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      {#if formErrors.phone}<p class="text-red-400 mt-1 text-xs">{(formErrors.phone).join(', ')}</p>{/if}
    </div>

    <button 
      type="submit" 
      disabled={submitting}
      class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition disabled:opacity-50"
    >
      {submitting ? 'Menyimpan...' : 'Simpan Perubahan'}
    </button>
  </form>
  {/if}
</div>