<script>
  let businessName = $state('UMKM Snack Sejahtera');
  let category = $state('Kuliner & Makanan');
  let phone = $state('081234567890');

  function handleSave(e) {
    e.preventDefault();
    alert('Profil usaha berhasil diperbarui!');
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-3xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Profil Usaha & Tagihan</h1>
    <p class="text-xs md:text-sm text-slate-400">Atur informasi usaha UMKM dan metode pembayaran untuk pengisian saldo Escrow.</p>
  </div>

  <form onsubmit={handleSave} class="p-6 bg-slate-900/60 border border-slate-800/80 rounded-2xl backdrop-blur-xl space-y-5">
    <div>
      <label for="bname" class="block font-medium text-slate-300 text-xs mb-1.5">Nama Usaha / Bisnis</label>
      <input 
        id="bname"
        type="text" 
        bind:value={businessName}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
    </div>

    <div>
      <label for="bcat" class="block font-medium text-slate-300 text-xs mb-1.5">Kategori Bidang Usaha</label>
      <input 
        id="bcat"
        type="text" 
        bind:value={category}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
    </div>

    <div>
      <label for="phone" class="block font-medium text-slate-300 text-xs mb-1.5">Nomor WhatsApp PIC</label>
      <input 
        id="phone"
        type="text" 
        bind:value={phone}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
    </div>

    <button 
      type="submit" 
      class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition"
    >
      Simpan Perubahan
    </button>
  </form>
</div>