<script>
  import { maintenance } from '$lib/stores/maintenance.svelte.js';

  let { } = $props();
</script>

<div class="maintenance-screen font-sans">
  <div class="ms-card">
    <div class="ms-icon">🔧</div>
    <h1 class="ms-title">Kami Sedang Maintenance</h1>
    <p class="ms-message">{maintenance.message}</p>
    <p class="ms-sub">Tim Kerjain sedang melakukan perbaikan dan pemeliharaan sistem. Halaman akan kembali normal dalam beberapa saat.</p>
  </div>
</div>

<style>
  .maintenance-screen {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: linear-gradient(180deg, #0d233a 0%, #0f172a 100%);
    color: #f8fafc;
  }

  .ms-card {
    max-width: 460px;
    width: 100%;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid #334155;
    border-radius: 20px;
    padding: 48px 36px;
    text-align: center;
    backdrop-filter: blur(8px);
  }

  .ms-icon {
    font-size: 44px;
    margin-bottom: 16px;
  }

  .ms-title {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px;
    letter-spacing: -0.01em;
  }

  .ms-message {
    font-size: 14px;
    color: #fbbf24;
    font-weight: 600;
    margin: 0 0 10px;
    line-height: 1.5;
  }

  .ms-sub {
    font-size: 12.5px;
    color: #94a3b8;
    margin: 0;
    line-height: 1.6;
  }
</style>