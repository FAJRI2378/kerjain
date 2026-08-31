<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let jobs = $state([]);
  let loading = $state(true);
  let completingId = $state(null);

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/hire/jobs');
      jobs = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat pekerjaan.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  const badge = $derived((status) => {
    if (status === 'reviewing') return { label: 'Butuh Persetujuan', cls: 'bg-amber-500/10 text-amber-400 border-amber-500/20' };
    if (status === 'in_progress') return { label: 'Sedang Dikerjakan', cls: 'bg-blue-500/10 text-blue-400 border-blue-500/20' };
    if (status === 'completed') return { label: 'Selesai & Lunas', cls: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' };
    if (status === 'approved') return { label: 'Menunggu Freelancer', cls: 'bg-slate-500/10 text-slate-300 border-slate-500/20' };
    if (status === 'pending') return { label: 'Menunggu Moderasi', cls: 'bg-amber-500/10 text-amber-400 border-amber-500/20' };
    return { label: status, cls: 'bg-slate-500/10 text-slate-300 border-slate-500/20' };
  });

  async function approveWork(jobId, title) {
    if (!confirm('Apakah Anda yakin menyetujui hasil pengerjaan ini? Dana Escrow akan ditransfer ke Freelancer.')) return;
    completingId = jobId;
    try {
      await api.post(`/api/tasks/${jobId}/complete`, {});
      jobs = jobs.map((j) => (j.id === jobId ? { ...j, status: 'completed' } : j));
      toast(`Pekerjaan "${title}" selesai! Dana berhasil dicairkan ke Freelancer.`, 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal menyetujui pekerjaan.'), 'error');
    } finally {
      completingId = null;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Kelola Pekerjaan Saya</h1>
    <p class="text-xs md:text-sm text-slate-400">Review bukti pengerjaan tugas dari freelancer dan konfirmasi pencairan dana.</p>
  </div>

  <!-- List Jobs -->
  {#if loading}
    <div class="text-center text-slate-400 text-sm py-12">Memuat pekerjaan...</div>
  {:else if jobs.length === 0}
    <div class="py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-2xl">
      Belum ada pekerjaan. Buat tugas baru untuk mulai.
    </div>
  {:else}
  <div class="space-y-4">
    {#each jobs as job (job.id)}
      {@const b = badge(job.status)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] text-indigo-400 font-bold">#{job.id}</span>
            <span class={`px-2 py-0.5 border rounded text-[10px] font-bold ${b.cls}`}>{b.label}</span>
          </div>
          <h3 class="font-bold text-white text-base">{job.title}</h3>
          <p class="text-xs text-slate-400">Freelancer: <span class="text-slate-200 font-medium">{job.worker?.name ?? 'Belum ada'}</span></p>
          {#if job.proof_url}
            <a href={job.proof_url} target="_blank" rel="noreferrer" class="text-[11px] text-indigo-400 underline font-medium block pt-1">
              📎 Lihat Bukti Pekerjaan Freelancer
            </a>
          {/if}
        </div>

        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-slate-800">
          <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Nilai Kontrak</span>
            <span class="font-black text-indigo-400 text-sm">{formatRupiah(job.budget)}</span>
          </div>

          {#if job.status === 'reviewing'}
            <button
              onclick={() => approveWork(job.id, job.title)}
              disabled={completingId === job.id}
              class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition disabled:opacity-50"
            >
              {completingId === job.id ? 'Memproses...' : 'Approve & Cairkan'}
            </button>
          {:else if job.status === 'in_progress'}
            <span class="text-xs text-slate-500 font-medium italic">Menunggu Upload Bukti</span>
          {:else if job.status === 'completed'}
            <span class="text-xs text-emerald-400 font-bold">✓ Selesai</span>
          {:else}
            <span class="text-xs text-slate-500 font-medium italic">Menunggu proses</span>
          {/if}
        </div>
      </div>
    {/each}
  </div>
  {/if}
</div>