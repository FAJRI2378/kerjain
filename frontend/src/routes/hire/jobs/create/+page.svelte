<script>
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let title = $state('');
  let category = $state('');
  let location = $state('');
  let budget = $state(150000);
  let description = $state('');
  let categories = $state([]);
  let submitting = $state(false);
  let formErrors = $state({});

  onMount(async () => {
    try {
      const res = await api.get('/api/categories');
      categories = res.data ?? [];
      if (categories.length && !category) category = categories[0].slug;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat kategori.'), 'error');
    }
  });

  async function handleSubmit(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      await api.post('/api/tasks', {
        title,
        category,
        location,
        budget: Number(budget),
        description
      });
      toast(`Tugas "${title}" berhasil ditayangkan dan budget dipindahkan sementara ke Escrow!`, 'success');
      goto('/hire/jobs');
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Gagal membuat tugas.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-3xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Buat Tugas / Pekerjaan Baru</h1>
    <p class="text-xs md:text-sm text-slate-400">Post tugas harian atau sampingan untuk diselesaikan oleh freelancer terverifikasi.</p>
  </div>

  <form onsubmit={handleSubmit} class="p-6 bg-slate-900/60 border border-slate-800/80 rounded-2xl backdrop-blur-xl space-y-5">
    
    <!-- Title -->
    <div>
      <label for="title" class="block font-medium text-slate-300 text-xs mb-1.5">Judul Tugas / Pekerjaan</label>
      <input 
        id="title"
        type="text" 
        placeholder="Contoh: Jasa Sebar Brosur Toko 500 Lembar" 
        bind:value={title}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      {#if formErrors.title}<p class="text-red-400 mt-1 text-xs">{(formErrors.title).join(', ')}</p>{/if}
    </div>

    <!-- Category & Location -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="category" class="block font-medium text-slate-300 text-xs mb-1.5">Kategori</label>
        <select 
          id="category"
          bind:value={category}
          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        >
          {#each categories as cat (cat.id)}
            <option value={cat.slug}>{cat.name}</option>
          {/each}
        </select>
        {#if formErrors.category}<p class="text-red-400 mt-1 text-xs">{(formErrors.category).join(', ')}</p>{/if}
      </div>

      <div>
        <label for="location" class="block font-medium text-slate-300 text-xs mb-1.5">Lokasi Kerja</label>
        <input 
          id="location"
          type="text" 
          placeholder="Contoh: Remote atau Jakarta Selatan" 
          bind:value={location}
          required
          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        />
        {#if formErrors.location}<p class="text-red-400 mt-1 text-xs">{(formErrors.location).join(', ')}</p>{/if}
      </div>
    </div>

    <!-- Budget -->
    <div>
      <label for="budget" class="block font-medium text-slate-300 text-xs mb-1.5">Budget Honor Freelancer (Rp)</label>
      <input 
        id="budget"
        type="number" 
        min="50000"
        step="10000"
        bind:value={budget}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      {#if formErrors.budget}<p class="text-red-400 mt-1 text-xs">{(formErrors.budget).join(', ')}</p>{/if}
      <p class="text-[10px] text-slate-500 mt-1">Dana akan dikunci di Escrow dan hanya dikirim ke freelancer saat pekerjaan disetujui.</p>
    </div>

    <!-- Description -->
    <div>
      <label for="desc" class="block font-medium text-slate-300 text-xs mb-1.5">Detail Instruksi Tugas</label>
      <textarea 
        id="desc"
        rows="4"
        placeholder="Jelaskan instruksi pengerjaan, lokasi pengambilan berkas, atau kriteria khusus..." 
        bind:value={description}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      ></textarea>
      {#if formErrors.description}<p class="text-red-400 mt-1 text-xs">{(formErrors.description).join(', ')}</p>{/if}
    </div>

    <!-- Submit -->
    <button 
      type="submit" 
      disabled={submitting}
      class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition disabled:opacity-50"
    >
      {submitting ? 'Menayangkan...' : 'Tayangkan Tugas & Kunci Dana Escrow'}
    </button>
  </form>
</div>