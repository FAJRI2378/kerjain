<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let searchQuery = $state('');
  let selectedCategory = $state('all');
  let categories = $state([]);
  let jobs = $state([]);
  let loading = $state(true);
  let applyingId = $state(null);

  async function load() {
    loading = true;
    try {
      const [catRes, jobRes] = await Promise.all([
        api.get('/api/categories'),
        api.get('/api/tasks?per_page=50')
      ]);
      categories = catRes.data ?? [];
      jobs = jobRes.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat lowongan.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  let filteredJobs = $derived(
    jobs.filter(job => {
      const catSlug = job.category?.slug;
      const matchSearch =
        !searchQuery ||
        job.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (job.owner?.name ?? '').toLowerCase().includes(searchQuery.toLowerCase());
      const matchCategory = selectedCategory === 'all' || catSlug === selectedCategory;
      return matchSearch && matchCategory;
    })
  );

  let appliedIds = $state([]);

  async function applyJob(jobId, title) {
    applyingId = jobId;
    try {
      await api.post(`/api/tasks/${jobId}/apply`, {});
      appliedIds = [...appliedIds, jobId];
      toast(`Lamaran untuk "${title}" terkirim! Menunggu konfirmasi UMKM.`, 'success');
    } catch (err) {
      if (err.status === 409) {
        appliedIds = [...appliedIds, jobId];
        toast(err.message || 'Kamu sudah melamar tugas ini.', 'info');
      } else {
        toast(errorMessage(err, 'Gagal mengirim lamaran.'), 'error');
      }
    } finally {
      applyingId = null;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Eksplor Lowongan Tugas</h1>
    <p class="text-xs md:text-sm text-slate-400">Temukan tugas harian atau sampingan dari UMKM sekitar dan mulai hasilkan uang.</p>
  </div>

  <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
    <input
      type="text"
      placeholder="Cari kata kunci tugas atau nama usaha..."
      bind:value={searchQuery}
      class="w-full sm:w-80 px-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition"
    />

    <div class="flex gap-1.5 bg-slate-900/80 p-1 border border-slate-800 rounded-xl text-xs w-full sm:w-auto overflow-x-auto">
      <button onclick={() => selectedCategory = 'all'}
        class={`px-3 py-1.5 rounded-lg transition whitespace-nowrap ${selectedCategory === 'all' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white'}`}>
        Semua
      </button>
      {#each categories as cat (cat.id)}
        <button
          onclick={() => selectedCategory = cat.slug}
          class={`px-3 py-1.5 rounded-lg transition whitespace-nowrap ${selectedCategory === cat.slug ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white'}`}>
          {cat.name}
        </button>
      {/each}
    </div>
  </div>

  {#if loading}
    <div class="text-center text-slate-400 text-sm py-12">Memuat lowongan...</div>
  {:else}
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {#each filteredJobs as job (job.id)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl hover:border-slate-700 transition flex flex-col justify-between space-y-4">
        <div class="space-y-2">
          <div class="flex justify-between items-start gap-2">
            <span class="px-2.5 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-bold rounded-md">
              {job.category?.name ?? '-'}
            </span>
            <span class="text-[10px] text-slate-500 font-mono">📍 {job.location}</span>
          </div>

          <h3 class="font-bold text-white text-base leading-snug">{job.title}</h3>
          <p class="text-xs text-slate-400">Pemberi Kerja: <span class="text-slate-200 font-medium">{job.owner?.name ?? '-'}</span>{#if job.deadline}<span class="text-slate-500"> • ⏱️ {job.deadline}</span>{/if}</p>
        </div>

        <div class="pt-3 border-t border-slate-800/60 flex items-center justify-between">
          <div>
            <span class="text-[10px] text-slate-500 block">Honor Pekerjaan</span>
            <span class="text-lg font-black text-emerald-400">{formatRupiah(job.budget)}</span>
          </div>

          <button
            onclick={() => applyJob(job.id, job.title)}
            disabled={appliedIds.includes(job.id) || applyingId === job.id}
            class={`px-4 py-2 font-bold text-xs rounded-xl transition disabled:opacity-60 ${appliedIds.includes(job.id) ? 'bg-slate-800 text-slate-400' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-lg shadow-emerald-500/10'}`}>
            {appliedIds.includes(job.id) ? '✓ Sudah Dilamar' : applyingId === job.id ? 'Mengirim...' : 'Lamar Tugas'}
          </button>
        </div>
      </div>
    {:else}
      <div class="col-span-full py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-2xl">
        Tidak ada pekerjaan yang sesuai dengan kriteria pencarian.
      </div>
    {/each}
  </div>
  {/if}
</div>
