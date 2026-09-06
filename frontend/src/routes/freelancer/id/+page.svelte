<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let phoneNumber = $state('');
  let emailAddress = $state('');
  let bankName = $state('');
  let customBankName = $state('');
  let accountNumber = $state('');
  let accountHolderName = $state('');
  let isVerified = $derived(!!auth.user?.is_verified);
  let verification = $state(null);
  let loading = $state(true);
  let submitting = $state(false);
  let formErrors = $state({});

  const status = $derived(verification?.status ?? null);

  const bankOptions = [
    'BCA',
    'Bank Mandiri',
    'BRI',
    'BNI',
    'Bank Syariah Indonesia (BSI)',
    'Bank Jago',
    'GoPay',
    'OVO',
    'Dana',
    'Lainnya / Bank Lain'
  ];

  onMount(async () => {
    if (!auth.hydrated) auth.hydrate();
    await loadStatus();
    if (auth.user) {
      const data = verification?.data ?? {};
      phoneNumber = data.phone || auth.user.phone || '';
      emailAddress = data.email || auth.user.email || '';
      bankName = data.bank_name || '';
      accountNumber = data.account_number || '';
      accountHolderName = data.account_holder_name || auth.user.name || '';
      if (typeof window !== 'undefined' && bankName && !bankOptions.includes(bankName)) {
        customBankName = bankName;
        bankName = 'Lainnya / Bank Lain';
      }
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

  async function handleUpload(e) {
    e.preventDefault();

    if (!phoneNumber.trim()) {
      toast('Masukkan nomor HP yang aktif!', 'error');
      return;
    }
    if (!emailAddress.trim()) {
      toast('Masukkan alamat Gmail / Email!', 'error');
      return;
    }

    const finalBankName = bankName === 'Lainnya / Bank Lain' ? customBankName.trim() : bankName;
    if (!finalBankName) {
      toast('Pilih atau masukkan nama bank Anda!', 'error');
      return;
    }
    if (!accountNumber.trim()) {
      toast('Masukkan nomor rekening / E-Wallet!', 'error');
      return;
    }
    if (!accountHolderName.trim()) {
      toast('Masukkan nama pemegang rekening / E-Wallet!', 'error');
      return;
    }

    submitting = true;
    formErrors = {};

    try {
      const res = await api.post('/api/freelancer/verification', {
        phone: phoneNumber,
        email: emailAddress,
        bank_name: finalBankName,
        account_number: accountNumber,
        account_holder_name: accountHolderName
      });

      toast('Data verifikasi berhasil dikirim!', 'success');
      resetBankFields();
      await loadStatus();
    } catch (err) {
      console.error('Error kirim verifikasi:', err);

      if (err?.status === 422 && err?.data?.errors) {
        formErrors = err.data.errors;
        toast('Mohon periksa kembali inputan Anda.', 'error');
      } else {
        toast(errorMessage(err, 'Gagal mengirim verifikasi.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }

  function resetBankFields() {
    bankName = '';
    customBankName = '';
    accountNumber = '';
  }
</script>

<div class="verification-page">
  <!-- Top Banner / Header -->
  <div class="page-header">
    <h1 class="page-title">Verifikasi Identitas</h1>
    <p class="page-sub">Verifikasi identitas dan kontak kamu untuk membangun kepercayaan UMKM dan membuka akses melamar pekerjaan.</p>
  </div>

  {#if !loading}
    {#if isVerified}
      <div class="success-card">
        <div class="success-icon">🎉</div>
        <h3 class="success-title">Identitas Terverifikasi ✓</h3>
        <p class="success-sub">Akun kamu telah terverifikasi. Kamu bisa melamar pekerjaan yang tersedia di platform.</p>
      </div>
    {:else if status === 'pending'}
      <div class="success-card">
        <div class="success-icon">⏳</div>
        <h3 class="success-title">Data Verifikasi Sedang Direview</h3>
        <p class="success-sub">Tim Kerjain sedang memverifikasi data dan rekening kamu. Proses ini membutuhkan waktu maksimal 1x24 jam. Kamu masih bisa mengubah data dan mengirim ulang.</p>
      </div>
    {:else}
      <form onsubmit={handleUpload} class="verification-card" novalidate>

        {#if status === 'rejected'}
          <div class="alert-card">
            ⚠️ Pengajuan sebelumnya <strong>ditolak</strong>: "<em>{verification?.admin_note || 'Data kurang lengkap.'}</em>" Silakan perbaiki data di bawah dan kirim ulang.
          </div>
        {/if}

        <!-- Nomor HP -->
        <div class="form-group">
          <label for="phone-number">Nomor HP / WhatsApp <span class="req">*</span></label>
          <div class="input-wrapper">
            <input
              id="phone-number"
              type="tel"
              placeholder="Contoh: 081234567890"
              bind:value={phoneNumber}
              class="form-input"
            />
            <span class="input-icon">📱</span>
          </div>
          {#if formErrors.phone}
            <p class="error-msg">{(formErrors.phone).join(', ')}</p>
          {/if}
        </div>

        <!-- Gmail -->
        <div class="form-group">
          <label for="email-address">Alamat Gmail / Email <span class="req">*</span></label>
          <div class="input-wrapper">
            <input
              id="email-address"
              type="email"
              placeholder="nama@gmail.com"
              bind:value={emailAddress}
              class="form-input"
            />
            <span class="input-icon">✉️</span>
          </div>
          {#if formErrors.email}
            <p class="error-msg">{(formErrors.email).join(', ')}</p>
          {/if}
        </div>

        <!-- Pilihan Bank / E-Wallet -->
        <div class="form-group">
          <label for="bank-select">Bank / E-Wallet <span class="req">*</span></label>
          <select id="bank-select" bind:value={bankName} class="form-select">
            <option value="" disabled selected>Pilih Bank atau E-Wallet...</option>
            {#each bankOptions as bank}
              <option value={bank}>{bank}</option>
            {/each}
          </select>
          {#if formErrors.bank_name}
            <p class="error-msg">{(formErrors.bank_name).join(', ')}</p>
          {/if}
        </div>

        <!-- Input Tambahan Jika Memilih "Lainnya / Bank Lain" -->
        {#if bankName === 'Lainnya / Bank Lain'}
          <div class="form-group custom-bank-group">
            <label for="custom-bank">Nama Bank / E-Wallet Lainnya <span class="req">*</span></label>
            <div class="input-wrapper">
              <input
                id="custom-bank"
                type="text"
                placeholder="Contoh: Bank Permata, Seabank, LinkAja..."
                bind:value={customBankName}
                class="form-input"
              />
              <span class="input-icon">🏛️</span>
            </div>
          </div>
        {/if}

        <!-- Nomor Rekening -->
        <div class="form-group">
          <label for="account-number">No. Rekening / Nomor E-Wallet <span class="req">*</span></label>
          <div class="input-wrapper">
            <input
              id="account-number"
              type="text"
              inputmode="numeric"
              pattern="[0-9]*"
              placeholder="Contoh: 1234567890"
              bind:value={accountNumber}
              class="form-input"
            />
            <span class="input-icon">💳</span>
          </div>
          {#if formErrors.account_number}
            <p class="error-msg">{(formErrors.account_number).join(', ')}</p>
          {/if}
        </div>

        <!-- Nama Pemegang Rekening -->
        <div class="form-group">
          <label for="account-holder-name">Nama Sesuai Rekening / E-Wallet <span class="req">*</span></label>
          <div class="input-wrapper">
            <input
              id="account-holder-name"
              type="text"
              placeholder="Nama pemilik rekening, sesuai data bank"
              bind:value={accountHolderName}
              class="form-input"
            />
            <span class="input-icon">🪪</span>
          </div>
          {#if formErrors.account_holder_name}
            <p class="error-msg">{(formErrors.account_holder_name).join(', ')}</p>
          {/if}
        </div>

        <button
          type="submit"
          disabled={submitting}
          class="btn-submit"
        >
          {#if submitting}
            <span class="spinner-sm"></span> Mengirim Data...
          {:else}
            {status === 'rejected' ? 'Kirim Ulang Data Verifikasi' : 'Kirim Data Verifikasi'}
          {/if}
        </button>
      </form>
    {/if}
  {:else}
    <div class="success-card">
      <div class="success-icon">⏳</div>
      <h3 class="success-title">Memuat status verifikasi...</h3>
    </div>
  {/if}
</div>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
  }

  * {
    box-sizing: border-box;
  }

  .verification-page {
    max-width: 800px;
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
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
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

  .success-card {
    background: #ffffff;
    border: 1px solid #dcfce7;
    border-radius: 16px;
    padding: 40px 32px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(22, 101, 52, 0.05);
  }

  .success-icon {
    font-size: 40px;
    margin-bottom: 12px;
  }

  .success-title {
    font-size: 18px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 6px;
  }

  .success-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0 auto;
    max-width: 480px;
    line-height: 1.5;
  }

  .verification-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 32px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  .alert-card {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #7f1d1d;
    font-size: 13px;
    padding: 14px 16px;
    border-radius: 10px;
    line-height: 1.6;
  }

  .form-group {
    display: flex;
    flex-direction: column;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
  }

  .req {
    color: #dc2626;
  }

  .input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .form-input,
  .form-select {
    width: 100%;
    padding: 11px 14px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  .input-wrapper .form-input {
    padding-right: 40px;
  }

  .input-icon {
    position: absolute;
    right: 12px;
    font-size: 16px;
    pointer-events: none;
    opacity: 0.6;
  }

  .form-input:focus,
  .form-select:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%3C%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
  }

  .custom-bank-group {
    animation: fadeIn 0.2s ease-in-out;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .error-msg {
    color: #dc2626;
    font-size: 11.5px;
    margin: 4px 0 0;
    font-weight: 500;
  }

  .btn-submit {
    width: 100%;
    padding: 12px;
    background-color: #15803d;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background-color 0.2s, opacity 0.2s;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);
    margin-top: 8px;
  }

  .btn-submit:hover:not(:disabled) {
    background-color: #166534;
  }

  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .spinner-sm {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .verification-card),
  :global(body.dark-theme .success-card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .success-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .success-sub) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .alert-card) {
    background-color: #450a0a !important;
    border-color: #7f1d1d !important;
    color: #fecaca !important;
  }
  :global(body.dark-theme .form-input),
  :global(body.dark-theme .form-select) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .form-group label) {
    color: #cbd5e1 !important;
  }
</style>