<script>
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let currentPassword = $state('');
  let newPassword = $state('');
  let confirmPassword = $state('');
  let saving = $state(false);
  let errors = $state({});

  async function handleChange(e) {
    e.preventDefault();
    saving = true;
    errors = {};

    if (newPassword !== confirmPassword) {
      errors.password = ['Konfirmasi kata sandi tidak cocok.'];
      saving = false;
      return;
    }

    try {
      await api.post('/api/account/password', {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: confirmPassword
      });
      toast('Kata sandi berhasil diubah!', 'success');
      currentPassword = '';
      newPassword = '';
      confirmPassword = '';
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        errors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Gagal mengubah kata sandi.'), 'error');
      }
    } finally {
      saving = false;
    }
  }
</script>

<div class="card">
  <h3 class="card-title">🔑 Ubah Kata Sandi</h3>
  <p class="card-sub">Ganti kata sandi akun Anda. Setidaknya 8 karakter.</p>

  <form onsubmit={handleChange} class="form-grid">
    <div class="form-group">
      <label for="pw-current">Kata Sandi Saat Ini</label>
      <input id="pw-current" type="password" bind:value={currentPassword} required class="form-input" />
      {#if errors.current_password}<p class="error-text">{(errors.current_password).join(', ')}</p>{/if}
    </div>
    <div class="form-group">
      <label for="pw-new">Kata Sandi Baru</label>
      <input id="pw-new" type="password" bind:value={newPassword} required minlength="8" class="form-input" />
      {#if errors.password}<p class="error-text">{(errors.password).join(', ')}</p>{/if}
    </div>
    <div class="form-group">
      <label for="pw-confirm">Konfirmasi Kata Sandi Baru</label>
      <input id="pw-confirm" type="password" bind:value={confirmPassword} required class="form-input" />
    </div>
    <div class="form-actions full">
      <button type="submit" disabled={saving} class="btn-save">
        {saving ? 'Menyimpan...' : 'Ubah Kata Sandi'}
      </button>
    </div>
  </form>
</div>

<style>
  .card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .card-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
  }

  .card-sub {
    font-size: 12px;
    color: #64748b;
    margin: -8px 0 0;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }

  @media (min-width: 640px) {
    .form-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  .full {
    grid-column: 1 / -1;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
  }

  .form-input {
    width: 100%;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.3s ease;
  }

  .form-input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .error-text {
    color: #dc2626;
    font-size: 11.5px;
    margin: 0;
    font-weight: 500;
  }

  .form-actions {
    display: flex;
    justify-content: flex-end;
  }

  .btn-save {
    background: #15803d;
    color: #ffffff;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
  }

  .btn-save:hover:not(:disabled) {
    background: #166534;
  }

  .btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  :global(body.dark-theme .card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .card-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .card-sub) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .form-input) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .form-group label) {
    color: #cbd5e1 !important;
  }
</style>