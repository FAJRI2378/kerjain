<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  // State form verifikasi usaha
  let businessName = $state('');
  let businessAddress = $state('');
  let businessType = $state('Kuliner / F&B');
  let ownerName = $state('');
  let phone = $state('');

  let storePhotoPreview = $state('');
  let storePhotoFile = $state(null);
  let isSubmitting = $state(false);
  let loading = $state(true);

  let verification = $state(null);
  let isVerified = $derived(!!auth.user?.is_verified);

  const status = $derived(verification?.status ?? null);

  onMount(async () => {
    if (!auth.hydrated) auth.hydrate();
    await loadStatus();
    if (auth.user) {
      businessName = auth.user.business_profile?.business_name || auth.user.name || '';
      businessAddress = auth.user.business_profile?.address || auth.user.address || '';
      businessType = auth.user.business_profile?.business_type || businessType;
      ownerName = auth.user.name || '';
      phone = auth.user.phone || '';
      storePhotoPreview = auth.user.business_profile?.store_photo_url || auth.user.store_photo || '';
    }
  });

  async function loadStatus() {
    try {
      const res = await api.get('/api/verification/status');
      verification = res.data ?? null;
    } catch {
      verification = null;
    } finally {
      loading = false;
    }
  }

  function handleStorePhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
      storePhotoFile = file;
      storePhotoPreview = URL.createObjectURL(file);
    }
  }

  async function handleSubmit(e) {
    e.preventDefault();

    if (!storePhotoFile) {
      toast('Harap pilih foto toko terlebih dahulu.', 'error');
      return;
    }

    isSubmitting = true;

    try {
      const formData = new FormData();
      formData.append('business_name', businessName);
      formData.append('business_type', businessType);
      formData.append('address', businessAddress);
      formData.append('phone', phone);
      formData.append('name', ownerName);
      formData.append('store_photo', storePhotoFile);

      await api.post('/api/hire/verification', formData);
      toast('Data & foto tempat dagang berhasil dikirim untuk verifikasi!', 'success');
      storePhotoFile = null;
      storePhotoPreview = '';
      await loadStatus();
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengirim data verifikasi.'), 'error');
    } finally {
      isSubmitting = false;
    }
  }
</script>

<div class="verifikasi-page font-sans">
  <div class="page-header">
    <div>
      <h1 class="page-title">Verifikasi Tempat Usaha 🏪</h1>
      <p class="page-sub">Kirim data & foto tempat usaha fisik Anda untuk mendapatkan lencana Escrow Verified dan bisa posting tugas.</p>
    </div>
    {#if !loading}
      <div class="status-badge-container">
        {#if isVerified}
          <span class="badge badge-green">🛡️ Escrow Verified</span>
        {:else if status === 'pending'}
          <span class="badge badge-amber">⏳ Menunggu Review Admin</span>
        {:else if status === 'rejected'}
          <span class="badge badge-red">✕ Ditolak Admin</span>
        {:else}
          <span class="badge badge-red">⚠️ Belum Terverifikasi</span>
        {/if}
      </div>
    {/if}
  </div>

  {#if isVerified}
    <div class="card status-card">
      <h3 class="room-kicker">Akun Anda Sudah Terverifikasi ✓</h3>
      <p class="room-title">Anda dapat memposting tugas untuk pekerja di sekitar usaha Anda.</p>
    </div>
  {:else}
    {#if status === 'rejected'}
      <div class="card alert-card">
        <p class="alert-text">
          ⚠️ Pengajuan sebelumnya <strong>ditolak</strong>:
          <em>"{verification?.admin_note || 'Data kurang lengkap.'}"</em>
          Silakan perbaiki data dan unggah ulang foto, lalu ajukan kembali.
        </p>
      </div>
    {/if}

    <form onsubmit={handleSubmit} class="card form-card">
      <!-- Upload Foto Tempat Dagang -->
      <div class="photo-upload-container">
        <div class="photo-preview-box">
          {#if storePhotoPreview}
            <img src={storePhotoPreview} alt="Tempat Dagang" class="store-img" />
          {:else}
            <div class="photo-placeholder">
              <span>📷</span>
              <p>Belum ada foto</p>
            </div>
          {/if}
        </div>
        <div class="photo-instruction">
          <h3 class="section-label">Foto Tempat Usaha / Toko Fisik (wajib)</h3>
          <p class="section-tip">Unggah foto bagian depan toko, gerobak, atau tempat usaha Anda agar freelancer lebih percaya dan verifikasi lebih cepat.</p>
          <label for="store-photo-input" class="btn-upload">
            Pilih Foto Toko (JPG/PNG)
          </label>
          <input 
            id="store-photo-input" 
            type="file" 
            accept="image/png, image/jpeg, image/jpg, image/webp" 
            onchange={handleStorePhotoChange} 
            class="hidden-input"
          />
          {#if status === 'pending'}
            <p class="pending-tip">Menunggu review admin (maks. 1×24 jam). Anda bisa mengubah data & mengirim ulang jika ada revisi.</p>
          {/if}
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="biz-name">Nama Usaha / Toko</label>
          <input 
            id="biz-name" 
            type="text" 
            bind:value={businessName} 
            required 
            placeholder="Contoh: Soto Pak Budi" 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="biz-type">Kategori Sektor Usaha</label>
          <select id="biz-type" bind:value={businessType} class="form-input">
            <option value="Kuliner / F&B">Kuliner / F&B (Resto, Cafe, Kedai)</option>
            <option value="Retail & Toko Kelontong">Retail & Toko Kelontong</option>
            <option value="Fashion & Konveksi">Fashion & Konveksi</option>
            <option value="Jasa & Lainnya">Jasa & Lainnya</option>
          </select>
        </div>

        <div class="form-group">
          <label for="owner-name">Nama Pemilik / PIC</label>
          <input 
            id="owner-name" 
            type="text" 
            bind:value={ownerName} 
            required 
            placeholder="Nama lengkap Anda" 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="phone">Nomor WhatsApp Bisnis</label>
          <input 
            id="phone" 
            type="tel" 
            bind:value={phone} 
            required 
            placeholder="Contoh: 081234567890" 
            class="form-input" 
          />
        </div>
      </div>

      <div class="form-group full-width">
        <label for="biz-address">Alamat Lengkap Tempat Dagang / Operasional</label>
        <textarea 
          id="biz-address" 
          rows="3" 
          bind:value={businessAddress} 
          required 
          placeholder="Contoh: Jl. Sudirman No. 45, Kel. Menteng, Jakarta Pusat..." 
          class="form-input textarea"
        ></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" disabled={isSubmitting} class="btn-submit">
          {isSubmitting ? 'Mengirim Verifikasi...' : status === 'rejected' ? 'Ajukan Ulang Verifikasi Usaha' : 'Simpan & Ajukan Verifikasi Usaha'}
        </button>
      </div>
    </form>
  {/if}
</div>

<style>
  .verifikasi-page {
    max-width: 900px;
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
    padding: 28px 32px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  @media (min-width: 640px) {
    .page-header {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  .page-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
  }

  .badge {
    font-size: 12px;
    font-weight: 700;
    padding: 6px 12px;
    border-radius: 8px;
    display: inline-block;
  }

  .badge-green { background: #dcfce7; color: #166534; }
  .badge-amber { background: #fef3c7; color: #92400e; }
  .badge-red { background: #fee2e2; color: #991b1b; }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 28px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .status-card {
    text-align: center;
    padding: 48px 28px;
  }

  .room-kicker {
    font-size: 20px;
    font-weight: 800;
    color: #166534;
    margin: 0 0 8px;
  }

  .room-title {
    font-size: 14px;
    color: #64748b;
    margin: 0;
  }

  .alert-card {
    border-color: #fecaca;
    background: #fef2f2;
    padding: 20px 24px;
  }

  .alert-text {
    font-size: 13px;
    color: #7f1d1d;
    margin: 0;
    line-height: 1.6;
  }

  .form-card {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .photo-upload-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 20px;
    border-radius: 12px;
  }

  @media (min-width: 640px) {
    .photo-upload-container {
      flex-direction: row;
      align-items: center;
    }
  }

  .photo-preview-box {
    width: 140px;
    height: 100px;
    background: #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid #cbd5e1;
  }

  .store-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .photo-placeholder {
    text-align: center;
    color: #64748b;
    font-size: 11px;
  }

  .photo-placeholder span {
    font-size: 24px;
    display: block;
    margin-bottom: 2px;
  }

  .section-label {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 4px;
  }

  .section-tip {
    font-size: 12.5px;
    color: #64748b;
    margin: 0 0 12px;
  }

  .pending-tip {
    font-size: 12px;
    font-weight: 600;
    color: #b45309;
    margin: 12px 0 0;
  }

  .btn-upload {
    background: #15803d;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-block;
    transition: background 0.2s;
  }

  .btn-upload:hover {
    background: #166534;
  }

  .hidden-input {
    display: none;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  @media (min-width: 640px) {
    .form-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group.full-width {
    grid-column: 1 / -1;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
  }

  .form-input {
    width: 100%;
    padding: 11px 14px;
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

  .textarea {
    resize: vertical;
  }

  .form-actions {
    margin-top: 8px;
  }

  .btn-submit {
    width: 100%;
    background: #15803d;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
    transition: background 0.2s, opacity 0.2s;
  }

  .btn-submit:hover:not(:disabled) {
    background: #166534;
  }

  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .section-label) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .section-tip),
  :global(body.dark-theme .photo-placeholder),
  :global(body.dark-theme .room-title) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .room-kicker) {
    color: #4ade80 !important;
  }
  :global(body.dark-theme .alert-card) {
    background-color: #450a0a !important;
    border-color: #7f1d1d !important;
  }
  :global(body.dark-theme .alert-text) {
    color: #fecaca !important;
  }
  :global(body.dark-theme .form-group label) {
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .form-input),
  :global(body.dark-theme .photo-upload-container) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .photo-preview-box) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
</style>