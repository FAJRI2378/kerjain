<script>
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let businessName = $state('');
  let businessType = $state('Kuliner / F&B');
  let address = $state('');
  let photoFile = $state(null);
  let photoPreview = $state('');
  let saving = $state(false);

  $effect(() => {
    if (auth.user) {
      const bp = auth.user.business_profile;
      businessName = bp?.business_name || auth.user.name || '';
      businessType = bp?.business_type || bp?.category || 'Kuliner / F&B';
      address = bp?.address || '';
      if (!photoFile) {
        photoPreview = bp?.store_photo_url || bp?.store_photo || '';
      }
    }
  });

  function handlePhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
      photoFile = file;
      photoPreview = URL.createObjectURL(file);
    }
  }

  async function handleSave(e) {
    e.preventDefault();
    saving = true;
    try {
      const formData = new FormData();
      formData.append('business_name', businessName);
      formData.append('business_type', businessType);
      formData.append('business_category', businessType);
      formData.append('address', address);
      formData.append('business_address', address);
      formData.append('phone', auth.user?.phone ?? '');
      if (photoFile) {
        formData.append('store_photo', photoFile);
      }
      await auth.updateProfile(formData);
      toast('Profil usaha berhasil diperbarui!', 'success');
      photoFile = null;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memperbarui profil usaha.'), 'error');
    } finally {
      saving = false;
    }
  }
</script>

<div class="card">
  <h3 class="card-title">🏪 Profil Usaha</h3>
  <p class="card-sub">Informasi tempat usaha yang ditampilkan ke pekerja.</p>

  <div class="photo-row">
    <div class="photo-box">
      {#if photoPreview}
        <img src={photoPreview} alt="Tempat Usaha" class="photo-img" />
      {:else}
        <span class="photo-fallback">📷</span>
      {/if}
    </div>
    <div>
      <label for="store-input" class="btn-upload">Ganti Foto Toko</label>
      <p class="photo-hint">JPG/PNG maks. 5MB — tampak depan toko.</p>
    </div>
  </div>
  <input id="store-input" type="file" accept="image/jpeg,image/png,image/webp" onchange={handlePhotoChange} class="hidden-input" />

  <form onsubmit={handleSave} class="form-grid">
    <div class="form-group">
      <label for="biz-name">Nama Usaha / Toko</label>
      <input id="biz-name" type="text" bind:value={businessName} required class="form-input" />
    </div>
    <div class="form-group">
      <label for="biz-type">Kategori Sektor Usaha</label>
      <select id="biz-type" bind:value={businessType} class="form-input">
        <option value="Kuliner / F&B">Kuliner / F&B</option>
        <option value="Retail & Toko Kelontong">Retail & Toko Kelontong</option>
        <option value="Fashion & Konveksi">Fashion & Konveksi</option>
        <option value="Jasa & Lainnya">Jasa & Lainnya</option>
      </select>
    </div>
    <div class="form-group full">
      <label for="biz-address">Alamat Lengkap</label>
      <textarea id="biz-address" rows="3" bind:value={address} required class="form-input"></textarea>
    </div>
    <div class="form-actions full">
      <button type="submit" disabled={saving} class="btn-save">
        {saving ? 'Menyimpan...' : 'Simpan Profil Usaha'}
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

  .photo-row {
    display: flex;
    align-items: center;
    gap: 14px;
  }

  .photo-box {
    width: 96px;
    height: 72px;
    border-radius: 10px;
    background: #e2e8f0;
    border: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
  }

  .photo-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .photo-fallback {
    font-size: 22px;
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
    display: inline-block;
  }

  .btn-upload:hover {
    background: #e2e8f0;
  }

  .photo-hint {
    font-size: 11px;
    color: #94a3b8;
    margin: 6px 0 0;
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
  :global(body.dark-theme .photo-box) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
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