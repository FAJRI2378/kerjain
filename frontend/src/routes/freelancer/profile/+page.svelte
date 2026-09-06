<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import AccountCard from '$lib/components/profile/account-card.svelte';
  import ChangePasswordCard from '$lib/components/profile/change-password-card.svelte';
  import VerificationStatusCard from '$lib/components/profile/verification-status-card.svelte';

  let verification = $state(null);
  let loading = $state(true);

  onMount(async () => {
    if (!auth.hydrated) auth.hydrate();
    try {
      const res = await api.get('/api/verification/status');
      verification = res.data ?? null;
    } catch {
      verification = null;
    } finally {
      loading = false;
    }
  });
</script>

<div class="profile-page font-sans">
  <div class="page-header">
    <h1 class="page-title">Profil & Verifikasi Identitas</h1>
    <p class="page-sub">Kelola akun dan status verifikasi untuk melamar pekerjaan.</p>
  </div>

  {#if loading}
    <p class="loading-text">Memuat profil...</p>
  {:else}
    <div class="grid">
      <VerificationStatusCard role="freelancer" isVerified={!!auth.user?.is_verified} {verification} />
      <AccountCard />
      <ChangePasswordCard />
    </div>
  {/if}
</div>

<style>
  .profile-page {
    max-width: 860px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 28px;
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .page-title {
    font-size: 22px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  .page-sub {
    font-size: 13px;
    color: #64748b;
    margin: 0;
  }

  .loading-text {
    text-align: center;
    padding: 40px;
    color: #64748b;
    font-size: 13px;
  }

  .grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
  }

  @media (min-width: 768px) {
    .grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  :global(body.dark-theme .page-header) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub) {
    color: #94a3b8 !important;
  }
</style>