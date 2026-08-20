<script>
  import { goto } from '$app/navigation';

  let title = $state('');
  let category = $state('Pemasaran');
  let location = $state('Jakarta Selatan');
  let budget = $state(150000);
  let description = $state('');

  function handleSubmit(e) {
    e.preventDefault();
    alert(`Tugas "${title}" berhasil ditayangkan dan budget Rp ${budget.toLocaleString('id-ID')} dipindahkan sementara ke Escrow!`);
    goto('/hire/jobs');
  }
</script>

<div class="p-6 md:p-10 space-y-6 font-sans max-w-3xl">
  <!-- Header -->
  <div class="border-b border-slate-800/80 pb-6">
    <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Buat Tugas / Pekerjaan Baru</h1>
    <p class="text-xs md:text-sm text-slate-400">Post tugas harian atau sampingan untuk diselesaikan oleh freelancer terverifikasi.</p>
  </div>

  <form onsubmit={handleSubmit} class="p-6 bg-slate-900/60 border border-slate-800/80 rounded-2xl backdrop-blur-xl space-y-5">
    
    <!-- Title -->
    <div>
      <label for="title" class="block font-medium text-slate-300 text-xs mb-1.5">Judul Tugas / Pekerjaan</label>
      <input 
        id="title"
        type="text" 
        placeholder="Contoh: Jasa Sebar Brosur Toko 500 Lembar" 
        bind:value={title}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
    </div>

    <!-- Category & Location -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label for="category" class="block font-medium text-slate-300 text-xs mb-1.5">Kategori</label>
        <select 
          id="category"
          bind:value={category}
          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        >
          <option value="Pemasaran">Pemasaran & Sebar Brosur</option>
          <option value="Fotografi">Fotografi & Video Produk</option>
          <option value="Live Host">Live Host Sales</option>
          <option value="Admin">Admin & Input Data</option>
          <option value="Desain">Desain Grafis</option>
        </select>
      </div>

      <div>
        <label for="location" class="block font-medium text-slate-300 text-xs mb-1.5">Lokasi Kerja</label>
        <input 
          id="location"
          type="text" 
          placeholder="Contoh: Remote atau Jakarta Selatan" 
          bind:value={location}
          required
          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
        />
      </div>
    </div>

    <!-- Budget -->
    <div>
      <label for="budget" class="block font-medium text-slate-300 text-xs mb-1.5">Budget Honor Freelancer (Rp)</label>
      <input 
        id="budget"
        type="number" 
        min="50000"
        step="10000"
        bind:value={budget}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      />
      <p class="text-[10px] text-slate-500 mt-1">Dana akan dikunci di Escrow dan hanya dikirim ke freelancer saat pekerjaan disetujui.</p>
    </div>

    <!-- Description -->
    <div>
      <label for="desc" class="block font-medium text-slate-300 text-xs mb-1.5">Detail Instruksi Tugas</label>
      <textarea 
        id="desc"
        rows="4"
        placeholder="Jelaskan instruksi pengerjaan, lokasi pengambilan berkas, atau kriteria khusus..." 
        bind:value={description}
        required
        class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500 transition"
      ></textarea>
    </div>

    <!-- Submit -->
    <button 
      type="submit" 
      class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-indigo-600/20 transition"
    >
      Tayangkan Tugas & Kunci Dana Escrow
    </button>
  </form>
</div>