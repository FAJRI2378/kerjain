<script>
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let email = $state('');
  let password = $state('');
  let showPassword = $state(false);
  let submitting = $state(false);
  let formErrors = $state({});

  onMount(async () => {
    if (!auth.hydrated) await auth.hydrate();
    if (auth.user?.role === 'admin') goto('/admin/dashboard', { replaceState: true });
  });

  async function handleAdminLogin(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      await auth.login({ role: 'admin', email, password });
      toast('Selamat datang, Admin!', 'success');
      goto('/admin/dashboard');
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Gagal masuk. Periksa kembali data Anda.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="relative min-h-screen bg-slate-950 text-white flex items-center justify-center p-4 overflow-hidden selection:bg-purple-500 selection:text-white">
  <div class="absolute -top-32 -right-32 w-96 h-96 bg-purple-500/10 blur-[120px] rounded-full pointer-events-none"></div>
  <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-indigo-500/10 blur-[120px] rounded-full pointer-events-none"></div>

  <div class="relative z-10 w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-2xl p-8 rounded-3xl shadow-2xl space-y-6">

    <div>
      <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition group">
        <span class="w-7 h-7 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center group-hover:border-slate-700 group-hover:bg-slate-800 transition">←</span>
        Kembali ke Beranda
      </a>
    </div>

    <div class="text-center space-y-2">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-purple-600/20 border border-purple-500/30 flex items-center justify-center overflow-hidden shadow-lg shadow-purple-500/20">
        <img src="/images/kerjain.webp" alt="Logo Kerjain" class="w-full h-full object-cover" />
      </div>
      <h1 class="text-2xl font-black tracking-tight text-white">Admin Workspace</h1>
      <p class="text-xs font-mono uppercase tracking-widest text-purple-400 font-bold">KERJAIN Portal Master</p>
    </div>

    <form onsubmit={handleAdminLogin} class="space-y-4 text-xs">
      <div>
        <label for="admin-email" class="block font-medium text-slate-300 mb-1.5">Email Admin</label>
        <input id="admin-email" type="email" required placeholder="admin@kerjain.id" bind:value={email}
          class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition" />
        {#if formErrors.email}<p class="text-red-400 mt-1">{(formErrors.email).join(', ')}</p>{/if}
      </div>

      <div>
        <label for="admin-password" class="block font-medium text-slate-300 mb-1.5">Kata Sandi</label>
        <div class="relative">
          <input id="admin-password" type={showPassword ? 'text' : 'password'} required placeholder="• • • • • • • •" bind:value={password}
            class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition pr-10" />
          <button type="button" onclick={() => showPassword = !showPassword} class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition text-xs">{showPassword ? 'Sembunyi' : 'Lihat'}</button>
        </div>
        {#if formErrors.password}<p class="text-red-400 mt-1">{(formErrors.password).join(', ')}</p>{/if}
      </div>

      <button type="submit" disabled={submitting}
        class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl shadow-lg shadow-purple-600/20 transition duration-200 mt-2 disabled:opacity-50">
        {submitting ? 'Memproses...' : 'Masuk ke Admin'}
      </button>
    </form>

    <div class="text-center text-[10px] text-slate-600">
      Area terbatas — hanya untuk admin KERJAIN.
    </div>
  </div>
</div>