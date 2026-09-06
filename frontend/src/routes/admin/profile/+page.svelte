<script>
  import AccountCard from '$lib/components/profile/account-card.svelte';
  import ChangePasswordCard from '$lib/components/profile/change-password-card.svelte';
</script>

<div class="admin-profile-page p-6 md:p-10 space-y-6 font-sans max-w-4xl">
  <div class="border-b border-slate-800/80 pb-6 profile-header">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight profile-title">Profil & Keamanan</h1>
    <p class="text-xs md:text-sm text-slate-400 profile-sub">Kelola data akun dan kata sandi admin.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <AccountCard />
    <ChangePasswordCard />
  </div>
</div>

<style>
  :global(body:not(.dark-theme)) .admin-profile-page {
    background-color: #f8fafc !important;
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .profile-header {
    border-color: #e2e8f0 !important;
  }

  :global(body:not(.dark-theme)) .profile-title {
    color: #0f172a !important;
  }

  :global(body:not(.dark-theme)) .profile-sub {
    color: #64748b !important;
  }

  :global(body.dark-theme .admin-profile-page .profile-title) {
    color: #ffffff !important;
  }
</style>