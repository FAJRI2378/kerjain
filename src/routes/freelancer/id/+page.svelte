<script>
  let idNumber = $state('');
  let isUploaded = $state(false);

  function handleUpload(e) {
    e.preventDefault();
    if (!idNumber) return alert('Masukkan Nomor NIK / KTP!');
    isUploaded = true;
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-3xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Verifikasi Identitas (KTP)</h1>
    <p class="text-xs md:text-sm text-slate-400">Verifikasi identitas kamu untuk membangun kepercayaan UMKM dan membuka akses ke semua tugas.</p>
  </div>

  {#if isUploaded}
    <div class="p-6 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl space-y-2 text-center">
      <span class="text-3xl">🎉</span>
      <h3 class="font-bold text-white text-base">Dokumen KTP Berhasil Dikirim!</h3>
      <p class="text-xs text-slate-300">Tim Kerjain sedang memverifikasi identitasmu. Proses verifikasi membutuhkan waktu maksimal 1x24 jam.</p>
    </div>
  {:else}
    <form onsubmit={handleUpload} class="p-6 bg-slate-900/60 border border-slate-800/80 rounded-2xl backdrop-blur-xl space-y-5">
      <div>
        <label for="nik" class="block font-medium text-slate-300 text-xs mb-1.5">Nomor Induk Kependudukan (NIK KTP)</label>
        <input 
          id="nik"
          type="text" 
          placeholder="16 Digit NIK KTP Kamu" 
          bind:value={idNumber}
          required
          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 transition"
        />
      </div>

      <div>
        <label for="ktp-file" class="block font-medium text-slate-300 text-xs mb-1.5">Foto KTP / Identitas</label>
        <div class="border-2 border-dashed border-slate-800 rounded-xl p-8 text-center bg-slate-950/40 hover:border-emerald-500/50 transition">
          <span class="text-2xl block mb-2">📸</span>
          <p class="text-xs font-semibold text-slate-300">Klik untuk unggah foto KTP</p>
          <p class="text-[10px] text-slate-500 mt-1">Format JPG atau PNG (Maks 5MB)</p>
          <input id="ktp-file" type="file" accept="image/*" class="hidden" />
        </div>
      </div>

      <button 
        type="submit" 
        class="w-full py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition"
      >
        Kirim Dokumen Verifikasi
      </button>
    </form>
  {/if}
</div>