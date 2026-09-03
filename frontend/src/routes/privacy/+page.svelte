<script>
  // Tanggal pembaruan terakhir
  const lastUpdated = "3 September 2026";

  const sections = [
    {
      id: "informasi-dikumpulkan",
      title: "1. Informasi yang Kami Kumpulkan",
      content: [
        "**Informasi Pribadi:** Nama, alamat email, nomor telepon, dan data profil yang Anda berikan saat mendaftar.",
        "**Informasi Proyek & Pekerjaan:** Detail deskripsi proyek, portofolio, tawaran harga, dan riwayat transaksi.",
        "**Data Penggunaan:** Alamat IP, jenis peramban (browser), halaman yang dikunjungi, dan waktu akses secara otomatis."
      ]
    },
    {
      id: "penggunaan-informasi",
      title: "2. Penggunaan Informasi",
      content: [
        "Memproses pendaftaran akun dan memverifikasi identitas pengguna.",
        "Menghubungkan pencari kerja (freelancer) dengan pemilik proyek.",
        "Mengirimkan notifikasi penting terkait transaksi, keamanan, dan pembaruan layanan.",
        "Meningkatkan kualitas UI/UX dan performa platform kami."
      ]
    },
    {
      id: "keamanan-data",
      title: "3. Perlindungan & Keamanan Data",
      content: [
        "Kami menerapkan langkah-langkah teknis dan organisasional yang ketat untuk melindungi data Anda dari akses, pengubahan, atau pengungkapan tanpa izin.",
        "Kami tidak menjual atau menyewakan informasi pribadi Anda kepada pihak ketiga untuk keperluan pemasaran tanpa persetujuan eksplisit dari Anda."
      ]
    },
    {
      id: "hak-pengguna",
      title: "4. Hak dan Kontrol Anda",
      content: [
        "Anda berhak memperbarui, mengoreksi, atau menghapus informasi profil Anda kapan saja melalui pengaturan akun.",
        "Anda dapat memilih untuk berhenti berlangganan pemberitahuan pemasaran melalui tautan di bagian bawah email kami."
      ]
    }
  ];
</script>

<div>
      <a href="/" class ="inline-min-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition group">
        <span class="w-7 h-7 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center group-hover:border-slate-700 group-hover:bg-slate-800 transition">←</span>
        Kembali ke Beranda
      </a>
    </div>

<svelte:head>
  <title>Kebijakan Privasi</title>
</svelte:head>

<section id="kebijakan-privasi" class="w-full max-w-4xl mx-auto px-4 py-12 text-slate-300">
  <!-- Header Halaman -->
  <div class="border-b border-slate-800 pb-8 mb-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">
      Kebijakan Privasi
    </h1>
    <p class="text-sm text-slate-400">
      Terakhir diperbarui: <span class="text-emerald-400 font-medium">{lastUpdated}</span>
    </p>
  </div>

  <!-- Pengantar -->
  <div class="mb-10 text-slate-300 leading-relaxed space-y-4">
    <p>
      Selamat datang di platform kami. Kami sangat menghargai privasi Anda dan berkomitmen untuk melindungi data pribadi yang Anda bagikan kepada kami. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi informasi Anda saat menggunakan layanan kami.
    </p>
  </div>

  <!-- Konten Kebijakan -->
  <div class="space-y-8">
    {#each sections as section (section.id)}
      <div id={section.id} class="bg-slate-900/50 border border-slate-800 rounded-xl p-6 sm:p-8">
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-4">
          {section.title}
        </h2>
        
        <ul class="space-y-3 text-slate-300 text-sm sm:text-base leading-relaxed">
          {#each section.content as point}
            <li class="flex items-start gap-2">
              <span class="text-emerald-400 font-bold mt-1">•</span>
              <span>{@html point.replace(/\*\*(.*?)\*\*/g, '<strong class="text-white font-semibold">$1</strong>')}</span>
            </li>
          {/each}
        </ul>
      </div>
    {/each}

    <!-- Hubungi Kami -->
    <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-6 sm:p-8 mt-12">
      <h2 class="text-lg font-bold text-white mb-2">Punya Pertanyaan Mengenai Privasi?</h2>
      <p class="text-sm text-slate-300 mb-4">
        Jika Anda memiliki pertanyaan atau kendala terkait Kebijakan Privasi ini, silakan hubungi tim dukungan kami.
      </p>
      <a 
        href="/kontak" 
        class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 hover:text-emerald-300 transition-colors"
      >
        Hubungi Tim Kami &rarr;
      </a>
    </div>
  </div>
</section>