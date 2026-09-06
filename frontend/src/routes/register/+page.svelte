<script>
  import { goto } from '$app/navigation';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { roleHome } from '$lib/role.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let role = $state('freelancer');
  let fullName = $state('');
  let businessName = $state('');
  let email = $state('');
  let password = $state('');
  let showPassword = $state(false);
  let submitting = $state(false);
  let formErrors = $state({});

  async function handleRegister(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      const payload = {
        role,
        name: fullName,
        email,
        password,
        ...(role === 'hirer' ? { business_name: businessName } : {})
      };
      await auth.register(payload);
      toast('Pendaftaran Berhasil!', 'success');
      goto(roleHome(role));
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Pendaftaran gagal. Silakan coba lagi.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="register-page">
  <!-- Decorative background elements -->
  <div class="bg-shape shape-a"></div>
  <div class="bg-shape shape-b"></div>

  <div class="register-card">
    <div class="card-header-actions">
      <a href="/" class="back-link">
        <span class="back-arrow">←</span>
        Kembali ke Beranda
      </a>
    </div>

    <div class="brand-heading">
      <a href="/" class="brand">
        <div class="brand-logo-wrap">
          <img src="/images/kerjain.webp" alt="Logo Kerjain" class="brand-img" />
        </div>
        <div class="brand-text">
          <span class="brand-name">KERJAIN</span>
          <span class="brand-tagline">Kerja kecil, dampak besar</span>
        </div>
      </a>
      <h1 class="page-title">Buat Akun Baru</h1>
      <p class="page-subtitle">Pilih jenis akun kamu dan mulai melangkah bersama Kerjain</p>
    </div>

    <!-- Role Switcher Tabs -->
    <div class="role-selector">
      <button 
        type="button" 
        onclick={() => role = 'freelancer'}
        class="role-btn {role === 'freelancer' ? 'active-freelancer' : ''}">
        <span>🛠️ Freelancer</span>
      </button>
      <button 
        type="button" 
        onclick={() => role = 'hirer'}
        class="role-btn {role === 'hirer' ? 'active-hirer' : ''}">
        <span>🏢 UMKM / Employer</span>
      </button>
    </div>

    <!-- Register Form -->
    <form onsubmit={handleRegister} class="register-form">
      <div class="form-group">
        <label for="fullName">Nama Lengkap</label>
        <input 
          id="fullName" 
          type="text" 
          required 
          placeholder="Ahmad Fauzi" 
          bind:value={fullName}
        />
        {#if formErrors.name}
          <p class="error-msg">{(formErrors.name).join(', ')}</p>
        {/if}
      </div>

      {#if role === 'hirer'}
        <div class="form-group">
          <label for="businessName">Nama Usaha / Toko</label>
          <input 
            id="businessName" 
            type="text" 
            required 
            placeholder="Kopi Hits Nusantara" 
            bind:value={businessName}
          />
          {#if formErrors.business_name}
            <p class="error-msg">{(formErrors.business_name).join(', ')}</p>
          {/if}
        </div>
      {/if}

      <div class="form-group">
        <label for="reg-email">Alamat Email</label>
        <input 
          id="reg-email" 
          type="email" 
          required 
          placeholder="nama@email.com" 
          bind:value={email}
        />
        {#if formErrors.email}
          <p class="error-msg">{(formErrors.email).join(', ')}</p>
        {/if}
      </div>

      <div class="form-group">
        <label for="reg-password">Kata Sandi</label>
        <div class="password-wrapper">
          <input 
            id="reg-password" 
            type={showPassword ? 'text' : 'password'} 
            required 
            placeholder="Minimal 8 karakter" 
            bind:value={password}
          />
          <button 
            type="button" 
            onclick={() => showPassword = !showPassword} 
            class="toggle-password">
            {showPassword ? 'Sembunyi' : 'Lihat'}
          </button>
        </div>
        {#if formErrors.password}
          <p class="error-msg">{(formErrors.password).join(', ')}</p>
        {/if}
      </div>

      <button 
        type="submit" 
        disabled={submitting}
        class="btn-submit">
        {submitting ? 'Memproses...' : `Daftar Sebagai ${role === 'freelancer' ? 'Freelancer' : 'UMKM'}`}
      </button>
    </form>

    <div class="card-footer">
      Sudah punya akun?
      <a href="/login" class="login-link">Masuk disini</a>
    </div>
  </div>
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

  .register-page {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 16px;
    background-color: #f8fafc;
    overflow: hidden;
  }

  /* Soft background shapes matching theme */
  .bg-shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    pointer-events: none;
    z-index: 0;
  }
  .shape-a {
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: rgba(16, 185, 129, 0.12);
  }
  .shape-b {
    bottom: -100px;
    left: -100px;
    width: 400px;
    height: 400px;
    background: rgba(13, 35, 58, 0.08);
  }

  .register-card {
    position: relative;
    z-index: 10;
    width: 100%;
    max-width: 440px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 10px 25px -5px rgba(13, 35, 58, 0.05), 0 8px 10px -6px rgba(13, 35, 58, 0.02);
  }

  /* Navigation & Branding */
  .card-header-actions {
    margin-bottom: 20px;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s;
  }
  .back-link:hover {
    color: #0d233a;
  }

  .back-arrow {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
  }

  .brand-heading {
    text-align: center;
    margin-bottom: 24px;
  }

  .brand {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    margin-bottom: 16px;
  }

  .brand-logo-wrap {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #0d233a;
    flex-shrink: 0;
  }

  .brand-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .brand-text {
    display: flex;
    flex-direction: column;
    text-align: left;
  }

  .brand-name {
    font-weight: 800;
    font-size: 16px;
    letter-spacing: 0.02em;
    color: #0d233a;
    line-height: 1.1;
  }

  .brand-tagline {
    font-size: 10px;
    color: #10b981;
    font-weight: 600;
  }

  .page-title {
    font-size: 22px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 6px;
    letter-spacing: -0.01em;
  }

  .page-subtitle {
    font-size: 13px;
    color: #64748b;
    margin: 0;
  }

  /* Role Switcher */
  .role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    padding: 4px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 24px;
  }

  .role-btn {
    border: none;
    background: transparent;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
  }

  .role-btn.active-freelancer {
    background: #15803d;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(21, 128, 61, 0.2);
  }

  .role-btn.active-hirer {
    background: #0d233a;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(13, 35, 58, 0.2);
  }

  /* Form Controls */
  .register-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
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

  .form-group input {
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

  .form-group input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }

  .password-wrapper input {
    padding-right: 70px;
  }

  .toggle-password {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
  }

  .toggle-password:hover {
    color: #0d233a;
  }

  .error-msg {
    color: #dc2626;
    font-size: 11.5px;
    margin: 4px 0 0;
    font-weight: 500;
  }

  /* Submit Button */
  .btn-submit {
    margin-top: 8px;
    width: 100%;
    padding: 12px;
    background-color: #15803d;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.2s, opacity 0.2s;
    box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);
  }

  .btn-submit:hover:not(:disabled) {
    background-color: #166534;
  }

  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* Footer */
  .card-footer {
    text-align: center;
    font-size: 12.5px;
    color: #64748b;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #f1f5f9;
  }

  .login-link {
    color: #15803d;
    font-weight: 700;
    text-decoration: none;
    margin-left: 4px;
  }

  .login-link:hover {
    text-decoration: underline;
  }
</style>