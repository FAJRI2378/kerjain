<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { waLink, waPhoneDisplay } from '$lib/whatsapp.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let myTasks = $state([]);
  let loading = $state(true);

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/tasks/mine');
      myTasks = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat kontak.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  const contacts = $derived(
    myTasks
      .filter((t) => t.owner?.name && waLink(t.owner.phone))
      .map((t) => ({
        taskId: t.id,
        taskTitle: t.title,
        status: t.status,
        budget: t.budget,
        name: t.owner.name,
        phone: t.owner.phone
      }))
  );
</script>

<svelte:head>
  <title>Kontak UMKM - Freelancer Portal</title>
</svelte:head>

<div class="kontak-page font-sans">
  <div class="page-header">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Portal Freelancer</span>
      </div>
      <h1 class="page-title">Kontak UMKM</h1>
      <p class="page-sub">Hubungi pemilik usaha / UMKM yang memberi tugas kepada Anda langsung melalui WhatsApp.</p>
    </div>
    <a href="/freelancer/mytasks" class="btn-back">← Kembali ke Tugas Saya</a>
  </div>

  {#if loading}
    <div class="state-box">
      <span class="spinner"></span>
      <p>Memuat kontak...</p>
    </div>
  {:else if contacts.length === 0}
    <div class="state-box">
      <p class="state-icon">🏢</p>
      <p class="state-title">Belum ada kontak UMKM</p>
      <p class="state-sub">Kontak UMKM akan muncul di sini setelah lamaran Anda diterima dan tugas berjalan.</p>
      <a href="/freelancer/jobs" class="btn-primary">Cari Tugas</a>
    </div>
  {:else}
    <div class="contact-list">
      {#each contacts as c (c.taskId)}
        <div class="contact-row">
          <div class="contact-avatar">{c.name.charAt(0)}</div>
          <div class="contact-info">
            <h3 class="contact-name">{c.name}</h3>
            <p class="contact-desc">📋 {c.taskTitle}</p>
            <div class="contact-meta">
              <span class="rate">💰 {formatRupiah(c.budget)}</span>
              <span class="status-pill {c.status === 'in_progress' ? 'blue' : c.status === 'reviewing' ? 'amber' : 'green'}">
                {c.status === 'in_progress' ? '🚧 Dalam Pengerjaan' : c.status === 'reviewing' ? '🔍 Menunggu Review' : '✅ Selesai'}
              </span>
            </div>
            <p class="contact-phone">📞 {waPhoneDisplay(c.phone)}</p>
          </div>
          <a
            href={waLink(c.phone, `Halo ${c.name}, saya dari Kerjain mengerjakan tugas \"${c.taskTitle}\" (#${c.taskId}).`)}
            target="_blank" rel="noreferrer"
            class="btn-wa"
          >
            💬 WhatsApp
          </a>
        </div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .kontak-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 28px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  }
  @media (min-width: 640px) {
    .page-header { flex-direction: row; align-items: center; justify-content: space-between; }
  }

  .page-title { font-size: 22px; font-weight: 800; color: #0d233a; margin: 0 0 4px; }
  .page-sub { font-size: 13px; color: #64748b; margin: 0; }
  .btn-back { font-size: 13px; font-weight: 700; color: #15803d; text-decoration: none; white-space: nowrap; }
  .btn-back:hover { text-decoration: underline; }

  .state-box {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 16px;
    padding: 56px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }
  .spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #dcfce7;
    border-top-color: #15803d;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .state-icon { font-size: 34px; margin: 0; }
  .state-title { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0; }
  .state-sub { font-size: 12px; color: #64748b; margin: 0 0 6px; }
  .btn-primary {
    display: inline-block;
    background: #15803d;
    color: #fff;
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
  }
  .btn-primary:hover { background: #166534; }

  .contact-list { display: flex; flex-direction: column; gap: 14px; }

  .contact-row {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
  }
  @media (max-width: 640px) {
    .contact-row { flex-direction: column; align-items: flex-start; }
  }

  .contact-avatar {
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 14px;
    background: linear-gradient(135deg, #15803d, #0d9488);
    color: #fff;
    font-size: 18px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .contact-info { flex: 1; min-width: 0; }
  .contact-name { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0 0 2px; }
  .contact-desc { font-size: 12px; color: #475569; margin: 0 0 6px; }
  .contact-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; }
  .rate { font-size: 12px; font-weight: 700; color: #15803d; }
  .status-pill { font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
  .status-pill.blue { background: #eff6ff; color: #2563eb; }
  .status-pill.amber { background: #fef3c7; color: #92400e; }
  .status-pill.green { background: #dcfce7; color: #166534; }
  .contact-phone { font-size: 12px; font-weight: 600; color: #334155; margin: 0; }

  .btn-wa {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #25d366;
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
  }
  .btn-wa:hover { background: #1eb858; }

  /* Dark Mode */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .contact-row),
  :global(body.dark-theme .state-box) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .contact-name),
  :global(body.dark-theme .state-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .contact-desc),
  :global(body.dark-theme .state-sub) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .contact-phone) { color: #e2e8f0 !important; }
</style>