<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let myTasks = $state([]);
  let loading = $state(true);
  let proofOpen = $state({});
  let proofInput = $state({});
  let submittingId = $state(null);

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/tasks/mine');
      myTasks = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat tugas.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  const statusBadge = $derived((status) => {
    if (status === 'in_progress') return { label: 'Pengerjaan', cls: 'bg-amber-500/10 text-amber-400 border-amber-500/20' };
    if (status === 'reviewing') return { label: 'Sedang Direview', cls: 'bg-blue-500/10 text-blue-400 border-blue-500/20' };
    return null;
  });

  async function submitProof(taskId) {
    const url = (proofInput[taskId] ?? '').trim();
    if (!url) {
      toast('Masukkan link bukti pekerjaan terlebih dahulu.', 'error');
      return;
    }
    submittingId = taskId;
    try {
      await api.post(`/api/tasks/${taskId}/submit`, { proof_url: url });
      myTasks = myTasks.map((t) => (t.id === taskId ? { ...t, status: 'reviewing', proof_url: url } : t));
      proofOpen[taskId] = false;
      toast('Bukti kerja berhasil dikirim ke Pemberi Kerja!', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengirim bukti.'), 'error');
    } finally {
      submittingId = null;
    }
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans">
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Tugas Saya</h1>
    <p class="text-xs md:text-sm text-slate-400">Pantau status pengerjaan tugas dan kirim bukti hasil kerja untuk pencairan dana.</p>
  </div>

  {#if loading}
    <div class="text-center text-slate-400 text-sm py-12">Memuat tugas...</div>
  {:else if myTasks.length === 0}
    <div class="py-12 text-center text-slate-500 text-xs border border-dashed border-slate-800 rounded-2xl">
      Belum ada tugas yang kamu kerjakan. Cek daftar lowongan untuk mulai.
    </div>
  {:else}
  <div class="space-y-4">
    {#each myTasks as task (task.id)}
      {@const badge = statusBadge(task.status)}
      <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-[10px] text-emerald-400 font-bold">#{task.id}</span>
            {#if badge}
              <span class={`px-2 py-0.5 border rounded text-[10px] font-bold ${badge.cls}`}>{badge.label}</span>
            {/if}
          </div>
          <h3 class="font-bold text-white text-base">{task.title}</h3>
          <p class="text-xs text-slate-400">UMKM: <span class="text-slate-200 font-medium">{task.owner?.name ?? '-'}</span></p>
          {#if task.proof_url}<p class="text-xs text-emerald-400"><a href={task.proof_url} target="_blank" rel="noreferrer" class="underline">📎 Lihat Bukti Dikirim</a></p>{/if}
        </div>

        <div class="flex items-center justify-between md:justify-end gap-4 border-t md:border-t-0 pt-3 md:pt-0 border-slate-800">
          <div class="text-right">
            <span class="text-[10px] text-slate-500 block">Bayaran</span>
            <span class="font-black text-emerald-400 text-sm">{formatRupiah(task.budget)}</span>
          </div>

          {#if task.status === 'in_progress'}
            <div class="flex items-center gap-2">
              {#if proofOpen[task.id]}
                <input
                  bind:value={proofInput[task.id]}
                  placeholder="Link Drive / Imgur / Dokumen"
                  class="w-56 px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                />
                <button onclick={() => submitProof(task.id)} disabled={submittingId === task.id}
                  class="px-3 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-lg disabled:opacity-50">
                  {submittingId === task.id ? 'Mengirim...' : 'Kirim'}
                </button>
              {:else}
                <button onclick={() => proofOpen[task.id] = true}
                  class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/10 transition">
                  Upload Bukti
                </button>
              {/if}
            </div>
          {:else}
            <span class="text-xs text-slate-400 font-semibold bg-slate-950 px-3 py-2 rounded-xl border border-slate-800">
              ⏳ Menunggu Approvel UMKM
            </span>
          {/if}
        </div>
      </div>
    {/each}
  </div>
  {/if}
</div>
