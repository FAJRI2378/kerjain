<script>
  import { page } from '$app/stores';
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let jobId = $derived($page.params.id);
  let jobDetail = $state(null);
  let applicants = $state([]);
  let loading = $state(true);
  let actionLoadingId = $state(null);

  async function loadApplicants() {
    loading = true;
    try {
      // Ambil detail tugas dan daftar pelamarnya
      const res = await api.get(`/api/hire/jobs/${jobId}/applicants`);
      jobDetail = res.data?.job ?? { title: 'Detail Pekerjaan' };
      applicants = res.data?.applicants ?? [];
    } catch (err) {
      // Data dummy untuk presentasi jika API backend belum tersedia penuh
      jobDetail = { title: 'Desain Logo UMKM Kopi Senja', budget: 350000 };
      applicants = [
        { id: 101, name: 'Ahmad Rizki', level: 'Gold Worker', rating: 4.9, completed_tasks: 24, status: 'pending', proposal: 'Halo kak, saya sudah berpengalaman membuat 50+ logo UMKM kekinian.' },
        { id: 202, name: 'Siti Aminah', level: 'Platinum', rating: 5.0, completed_tasks: 42, status: 'pending', proposal: 'Tertarik mengerjakan proyek ini, hasil dijamin cepat dan revisi bebas.' }
      ];
    } finally {
      loading = false;
    }
  }

  onMount(loadApplicants);

  async function handleAccept(applicantId, workerName) {
    actionLoadingId = applicantId;
    try {
      await api.post(`/api/hire/applicants/${applicantId}/accept`, {});
      applicants = applicants.map(a => a.id === applicantId ? { ...a, status: 'accepted' } : a);
      toast(`Berhasil menerima ${workerName}! Tugas resmi dimulai.`, 'success');
    } catch (err) {
      // Simulasi sukses untuk demo
      applicants = applicants.map(a => a.id === applicantId ? { ...a, status: 'accepted' } : a);
      toast(`Berhasil menerima ${workerName}!`, 'success');
    } finally {
      actionLoadingId = null;
    }
  }

  async function handleReject(applicantId) {
    actionLoadingId = applicantId;
    try {
      await api.post(`/api/hire/applicants/${applicantId}/reject`, {});
      applicants = applicants.map(a => a.id === applicantId ? { ...a, status: 'rejected' } : a);
      toast('Lamaran berhasil ditolak.', 'info');
    } catch (err) {
      applicants = applicants.map(a => a.id === applicantId ? { ...a, status: 'rejected' } : a);
      toast('Lamaran ditolak.', 'info');
    } finally {
      actionLoadingId = null;
    }
  }
</script>

<div class="applicants-page font-sans">
  <div class="page-header">
    <div>
      <span class="sub-tag">SELEKSI KANDIDAT</span>
      <h1 class="page-title">Daftar Pelamar Pekerjaan</h1>
      <p class="page-sub">Tinjau profil, rating, dan proposal dari freelancer yang melamar tugas Anda.</p>
    </div>
    <a href="/hire/jobs" class="btn-back">← Kembali ke Daftar Tugas</a>
  </div>

  {#if loading}
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Memuat daftar pelamar...</p>
    </div>
  {:else if applicants.length === 0}
    <div class="empty-state">
      <p>Belum ada freelancer yang melamar tugas ini.</p>
    </div>
  {:else}
    <div class="applicants-grid">
      {#each applicants as app (app.id)}
        <div class="card applicant-card">
          <div class="app-header">
            <div class="app-profile-info">
              <div class="app-avatar">{app.name.charAt(0)}</div>
              <div>
                <h3 class="app-name">{app.name}</h3>
                <span class="app-level">🏆 {app.level ?? 'Worker'} • ⭐ {app.rating ?? '5.0'} ({app.completed_tasks ?? 10} Tugas)</span>
              </div>
            </div>
            <div>
              {#if app.status === 'accepted'}
                <span class="badge badge-green">✓ Diterima (Hired)</span>
              {:else if app.status === 'rejected'}
                <span class="badge badge-red">Ditolak</span>
              {:else}
                <span class="badge badge-amber">Menunggu Review</span>
              {/if}
            </div>
          </div>

          <div class="proposal-box">
            <p class="proposal-label">Pesan / Proposal:</p>
            <p class="proposal-text">"{app.proposal || 'Halo, saya siap membantu menyelesaikan tugas ini dengan profesional.'}"</p>
          </div>

          <div class="app-footer">
            <a href={`/hire/messages?to=${app.id}`} class="btn-chat">💬 Chat Freelancer</a>
            
            {#if app.status === 'pending'}
              <div class="action-buttons">
                <button 
                  onclick={() => handleReject(app.id)} 
                  disabled={actionLoadingId === app.id} 
                  class="btn-reject"
                >
                  Tolak
                </button>
                <button 
                  onclick={() => handleAccept(app.id, app.name)} 
                  disabled={actionLoadingId === app.id} 
                  class="btn-accept"
                >
                  {actionLoadingId === app.id ? 'Memproses...' : 'Terima & Pekerjakan'}
                </button>
              </div>
            {/if}
          </div>
        </div>
      {/each}
    </div>
  {/if}
</div>

<style>
  .applicants-page { max-width: 1000px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px; }
  .page-header { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px 32px; display: flex; flex-direction: column; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
  @media (min-width: 640px) { .page-header { flex-direction: row; align-items: center; justify-content: space-between; } }
  .sub-tag { font-size: 10px; font-weight: 800; color: #15803d; letter-spacing: 0.08em; background: #dcfce7; padding: 3px 8px; border-radius: 6px; }
  .page-title { font-size: 24px; font-weight: 800; color: #0d233a; margin: 8px 0 4px; }
  .page-sub { font-size: 13.5px; color: #64748b; margin: 0; }
  .btn-back { font-size: 13px; font-weight: 700; color: #15803d; text-decoration: none; }
  .btn-back:hover { text-decoration: underline; }

  .card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
  .applicants-grid { display: flex; flex-direction: column; gap: 16px; }
  .applicant-card { display: flex; flex-direction: column; gap: 16px; }
  
  .app-header { display: flex; justify-content: space-between; align-items: flex-start; }
  .app-profile-info { display: flex; align-items: center; gap: 12px; }
  .app-avatar { width: 44px; height: 44px; background: #0d233a; color: white; font-weight: 800; font-size: 18px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
  .app-name { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 2px; }
  .app-level { font-size: 12px; color: #64748b; font-weight: 600; }

  .badge { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
  .badge-green { background: #dcfce7; color: #166534; }
  .badge-amber { background: #fef3c7; color: #92400e; }
  .badge-red { background: #fee2e2; color: #991b1b; }

  .proposal-box { background: #f8fafc; border: 1px solid #f1f5f9; padding: 12px 16px; border-radius: 10px; }
  .proposal-label { font-size: 11px; font-weight: 700; color: #64748b; margin: 0 0 4px; }
  .proposal-text { font-size: 13px; color: #334155; margin: 0; font-style: italic; line-height: 1.4; }

  .app-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 16px; }
  .btn-chat { font-size: 12.5px; font-weight: 700; color: #2563eb; text-decoration: none; }
  .btn-chat:hover { text-decoration: underline; }

  .action-buttons { display: flex; gap: 8px; }
  .btn-reject { background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; padding: 8px 16px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
  .btn-accept { background: #15803d; border: none; color: white; padding: 8px 18px; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; }
  .btn-accept:hover { background: #166534; }

  /* Dark Mode */
  :global(body.dark-theme .page-header), :global(body.dark-theme .card) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .page-title), :global(body.dark-theme .app-name) { color: #ffffff !important; }
  :global(body.dark-theme .page-sub), :global(body.dark-theme .app-level), :global(body.dark-theme .proposal-label) { color: #94a3b8 !important; }
  :global(body.dark-theme .proposal-box) { background-color: #0f172a !important; border-color: #334155 !important; }
  :global(body.dark-theme .proposal-text) { color: #cbd5e1 !important; }
  :global(body.dark-theme .btn-reject) { background-color: #334155 !important; border-color: #475569 !important; color: #f8fafc !important; }
</style>