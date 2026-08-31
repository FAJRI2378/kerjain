<script>
  import { goto } from '$app/navigation';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { roleHome } from '$lib/role.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let role = $state('freelancer');
  let email = $state('');
  let password = $state('');
  let showPassword = $state(false);
  let submitting = $state(false);
  let formErrors = $state({});

  async function handleLogin(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      await auth.login({ role, email, password });
      toast(`Selamat datang kembali!`, 'success');
      goto(roleHome(role));
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else if (err.status === 403) {
        toast(err.message, 'error');
      } else {
        toast(errorMessage(err, 'Gagal masuk. Periksa kembali data Anda.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="relative min-h-screen bg-slate-950 text-white flex items-center justify-center p-4 overflow-hidden selection:bg-emerald-500 selection:text-white">
  <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 blur-[120px] rounded-full pointer-events-none"></div>
  <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-500/10 blur-[120px] rounded-full pointer-events-none"></div>

  <div class="relative z-10 w-full max-w-md bg-slate-900/80 border border-slate-800 backdrop-blur-2xl p-8 rounded-3xl shadow-2xl space-y-6">

    <div>
      <a href="/" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition group">
        <span class="w-7 h-7 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center group-hover:border-slate-700 group-hover:bg-slate-800 transition">←</span>
        Kembali ke Beranda
      </a>
    </div>

    <div class="text-center space-y-2">
      <a href="/" class="inline-flex items-center gap-2 mb-1">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 flex items-center justify-center font-black text-slate-950 text-sm shadow-lg shadow-emerald-500/30">K</div>
        <span class="font-extrabold text-xl tracking-tight text-white">KERJAIN<span class="text-emerald-400">.</span></span>
      </a>
      <h1 class="text-2xl font-black tracking-tight text-white">Selamat Datang Kembali</h1>
      <p class="text-xs text-slate-400">Masuk ke akun kamu untuk melanjutkan</p>
    </div>

    <div class="grid grid-cols-2 gap-1 p-1 bg-slate-950/80 rounded-2xl border border-slate-800 text-xs font-semibold">
      <button type="button" onclick={() => role = 'freelancer'}
        class={`py-2 rounded-xl transition flex items-center justify-center gap-2 ${role === 'freelancer' ? 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20' : 'text-slate-400 hover:text-white'}`}><span>🛠️</span> Freelancer</button>
      <button type="button" onclick={() => role = 'hirer'}
        class={`py-2 rounded-xl transition flex items-center justify-center gap-2 ${role === 'hirer' ? 'bg-blue-500 text-slate-950 font-bold shadow-md shadow-blue-500/20' : 'text-slate-400 hover:text-white'}`}><span>🏢</span> UMKM</button>
    </div>

    <form onsubmit={handleLogin} class="space-y-4 text-xs">
      <div>
        <label for="email" class="block font-medium text-slate-300 mb-1.5">Alamat Email</label>
        <input id="email" type="email" required placeholder="nama@email.com" bind:value={email}
          class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition" />
        {#if formErrors.email}<p class="text-red-400 mt-1">{(formErrors.email).join(', ')}</p>{/if}
      </div>

      <div>
        <div class="flex justify-between items-center mb-1.5">
          <label for="password" class="font-medium text-slate-300">Kata Sandi</label>
        </div>
        <div class="relative">
          <input id="password" type={showPassword ? 'text' : 'password'} required placeholder="• • • • • • • •" bind:value={password}
            class="w-full px-4 py-3 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition pr-10" />
          <button type="button" onclick={() => showPassword = !showPassword} class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition text-xs">{showPassword ? 'Sembunyi' : 'Lihat'}</button>
        </div>
        {#if formErrors.password}<p class="text-red-400 mt-1">{(formErrors.password).join(', ')}</p>{/if}
      </div>

      <button type="submit" disabled={submitting}
        class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-emerald-500/20 transition duration-200 mt-2 disabled:opacity-50">
        {submitting ? 'Memproses...' : 'Masuk Akun'}
      </button>
    </form>

    <div class="text-center text-xs text-slate-400 pt-2 border-t border-slate-800/60">
      Belum punya akun?
      <a href="/register" class="text-emerald-400 font-semibold hover:underline">Daftar sekarang</a>
    </div>

    <div class="text-center text-[10px] text-slate-600">
      <a href="/admin/login" class="hover:text-slate-400 transition">Karyawan / Staff? Masuk disini</a>
    </div>
  </div>
</div>
