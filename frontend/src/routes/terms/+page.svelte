<script>
  const lastUpdated = "3 September 2026";

  const sections = [
    {
      id: "ketentuan-umum",
      title: "1. Ketentuan Umum",
      content: [
        "Dengan mengakses dan menggunakan platform ini, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini serta Kebijakan Privasi kami.",
        "Pengguna wajib berusia minimal **18 tahun** atau telah dewasa secara hukum sesuai peraturan undang-undang yang berlaku.",
        "Kami berhak mengubah atau memperbarui Syarat & Ketentuan ini kapan saja tanpa pemberitahuan sebelumnya. Perubahan akan berlaku segera setelah diunggah di halaman ini."
      ]
    },
    {
      id: "akun-pengguna",
      title: "2. Akun & Keamanan Pengguna",
      content: [
        "Anda bertanggung jawab penuh untuk menjaga kerahasiaan kredensial (username & password) akun Anda.",
        "Setiap informasi yang Anda berikan saat pendaftaran harus **akurat, lengkap, dan terbaru**.",
        "Kami berhak menangguhkan atau menghentikan akun Anda jika ditemukan pelanggaran, penyalahgunaan, atau indikasi penipuan."
      ]
    },
    {
      id: "proyek-pembayaran",
      title: "3. Ketentuan Proyek & Pembayaran",
      content: [
        "**Pemilik Proyek (Client)** wajib memberikan deskripsi kerja yang jelas dan melakukan penyetoran dana ke sistem *Escrow* (rekber) kami sebelum pekerjaan dimulai.",
        "**Pekerja (Freelancer)** wajib menyelesaikan proyek sesuai spesifikasi dan tenggat waktu (*deadline*) yang disepakati.",
        "Dana akan diteruskan ke Freelancer setelah Pekerjaan disetujui oleh Client atau setelah batas waktu garansi pemeriksaan berakhir.",
        "Biaya layanan (*service fee*) akan dikenakan pada setiap transaksi yang berhasil sesuai dengan tarif yang berlaku."
      ]
    },
    {
      id: "hak-kekayaan-intelektual",
      title: "4. Hak Kekayaan Intelektual",
      content: [
        "Setelah pembayaran penuh diselesaikan oleh Client, semua Hak Kekayaan Intelektual atas hasil karya (*deliverables*) beralih sepenuhnya kepada Client, kecuali ada kesepakatan tertulis lain.",
        "Freelancer berhak mencantumkan hasil karya dalam portofolio pribadi mereka, kecuali jika terikat oleh **NDA (Non-Disclosure Agreement)**."
      ]
    },
    {
      id: "larangan-pengguna",
      title: "5. Larangan & Pelanggaran",
      content: [
        "Pengguna dilarang melakukan transaksi pembayaran di luar sistem resmi platform (*off-site payment*).",
        "Dilarang mengunggah konten yang mengandung unsur SARA, pornografi, ujaran kebencian, *malware*, atau melanggar hukum.",
        "Dilarang memposting proyek atau keahlian palsu yang bertujuan untuk memanipulasi rating atau penipuan."
      ]
    },
    {
      id: "pembatasan-tanggung-jawab",
      title: "6. Pembatasan Tanggung Jawab",
      content: [
        "Platform ini disediakan sebagaimana adanya (*as-is*). Kami bertindak sebagai perantara dan tidak bertanggung jawab langsung atas perselisihan kualitas hasil kerja antara Client dan Freelancer.",
        "Kami memfasilitasi pusat bantuan (*dispute resolution*) untuk menengahi perselisihan, dan keputusan tim arbitrase kami bersifat final."
      ]
    }
  ];
</script>

<svelte:head>
  <title>Syarat & Ketentuan</title>
</svelte:head>

<section id="syarat-ketentuan" class="w-full max-w-4xl mx-auto px-4 py-12 text-slate-300">
  <!-- Header Halaman -->
  <div class="border-b border-slate-800 pb-8 mb-8">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-2">
      Syarat & Ketentuan
    </h1>
    <p class="text-sm text-slate-400">
      Terakhir diperbarui: <span class="text-emerald-400 font-medium">{lastUpdated}</span>
    </p>
  </div>

  <!-- Pengantar -->
  <div class="mb-10 text-slate-300 leading-relaxed">
    <p>
      Harap baca Syarat dan Ketentuan berikut dengan saksama sebelum menggunakan layanan kami. Dokumen ini mengatur penggunaan Anda atas platform dan layanan yang kami sediakan.
    </p>
  </div>

  <!-- Konten Syarat & Ketentuan -->
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

    <!-- Bantuan / Kontak -->
    <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-6 sm:p-8 mt-12">
      <h2 class="text-lg font-bold text-white mb-2">Ada Masalah atau Pertanyaan Hukum?</h2>
      <p class="text-sm text-slate-300 mb-4">
        Jika Anda memiliki pertanyaan seputar perselisihan transaksi atau pasal dalam Syarat & Ketentuan ini, hubungi tim arbitrase/legal kami.
      </p>
      <a 
        href="/kontak" 
        class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 hover:text-emerald-300 transition-colors"
      >
        Hubungi Tim Support &rarr;
      </a>
    </div>
  </div>
</section>