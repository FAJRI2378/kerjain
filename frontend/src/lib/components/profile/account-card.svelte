<script>
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let name = $state('');
  let email = $state('');
  let phone = $state('');
  let avatarFile = $state(null);
  let avatarPreview = $state('');
  let saving = $state(false);

  $effect(() => {
    if (auth.user) {
      name = auth.user.name ?? '';
      email = auth.user.email ?? '';
      phone = auth.user.phone ?? '';
      if (!avatarFile) {
        avatarPreview = auth.user.avatar_url || auth.user.avatar || '';
      }
    }
  });

  function handleAvatarChange(e) {
    const file = e.target.files[0];
    if (file) {
      avatarFile = file;
      avatarPreview = URL.createObjectURL(file);
    }
  }

  async function handleSave(e) {
    e.preventDefault();
    saving = true;
    try {
      const formData = new FormData();
      formData.append('name', name);
      formData.append('email', email);
      formData.append('phone', phone);
      if (avatarFile) {
        formData.append('avatar', avatarFile);
      }
      await auth.updateProfile(formData);
      toast('Data akun berhasil diperbarui!', 'success');
      avatarFile = null;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memperbarui akun.'), 'error');
    } finally {
      saving = false;
    }
  }
</script>

<div class="card">
  <h3 class="card-title">👤 Data Akun</h3>
  <p class="card-sub">Nama, email, nomor WhatsApp, dan foto profil.</p>

  <div class="avatar-row">
    <div class="avatar-box">
      {#if avatarPreview}
        <img src={avatarPreview} alt="Foto Profil" class="avatar-img" />
      {:else}
        <span class="avatar-fallback">{(name || auth.user?.name || '?').charAt(0).toUpperCase()}</span>
      {/if}
    </div>
    <label for="avatar-input" class="btn-upload">📷 Ubah Foto</label>
    <input id="avatar-input" type="file" accept="image/jpeg,image/png,image/webp" onchange={handleAvatarChange} class="hidden-input" />
  </div>

  <form onsubmit={handleSave} class="form-grid">
    <div class="form-group">
      <label for="acc-name">Nama Lengkap</label>
      <input id="acc-name" type="text" bind:value={name} required class="form-input" />
    </div>
    <div class="form-group">
      <label for="acc-email">Email</label>
      <input id="acc-email" type="email" bind:value={email} required class="form-input" />
    </div>
    <div class="form-group full">
      <label for="acc-phone">Nomor WhatsApp</label>
      <input id="acc-phone" type="tel" bind:value={phone} placeholder="081234567890" class="form-input" />
    </div>
    <div class="form-actions full">
      <button type="submit" disabled={saving} class="btn-save">
        {saving ? 'Menyimpan...' : 'Simpan Data Akun'}
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

  .avatar-row {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .avatar-box {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: linear-gradient(135deg, #15803d, #166534);
    color: #ffffff;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .avatar-fallback {
    font-size: 20px;
  }

  .btn-upload {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    padding: 7px 14px;
    border-radius: 8px;
    cursor: pointer;
  }

  .btn-upload:hover {
    background: #e2e8f0;
  }

  .hidden-input {
    display: none;
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
  :global(body.dark-theme .btn-upload) {
    background-color: #334155 !important;
    border-color: #475569 !important;
    color: #f8fafc !important;
  }
</style>