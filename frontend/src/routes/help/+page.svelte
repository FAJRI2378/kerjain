<script>
  import { slide } from 'svelte/transition';

  let searchQuery = $state('');
  let activeCategory = $state('semua');
  let openFaq = $state(null);

  const categories = [
    { id: 'semua', label: 'Semua Topic' },
    { id: 'umum', label: 'Umum' },
    { id: 'client', label: 'Untuk Client' },
    { id: 'freelancer', label: 'Untuk Freelancer' },
    { id: 'pembayaran', label: 'Pembayaran' }
  ];

  const faqs = [
    {
      id: 1,
      category: 'umum',
      question: 'Bagaimana cara kerja sistem Escrow (Rekber)?',
      answer: 'Sistem Escrow menampung dana Client secara aman saat proyek dimulai. Dana hanya akan dicairkan ke Freelancer setelah Client menyetujui hasil kerja atau masa garansi pemeriksaan selesai.'
    },
    {
      id: 2,
      category: 'client',
      question: 'Bagaimana cara merekrut Freelancer untuk proyek saya?',
      answer: 'Buat proyek baru melalui tombol "Buat Proyek", isi detail pekerjaan serta anggaran, lalu tunggu para Freelancer mengirimkan penawaran (proposal). Anda dapat meninjau portofolio mereka sebelum memilih.'
    },
    {
      id: 3,
      category: 'freelancer',
      question: 'Berapa potongan biaya layanan untuk Freelancer?',
      answer: 'Platform mengenakan biaya layanan sebesar 10% dari total nilai transaksi yang berhasil diselesaikan untuk pemeliharaan sistem dan perlindungan keamanan.'
    },
    {
      id: 4,
      category: 'pembayaran',
      question: 'Metode pembayaran apa saja yang didukung?',
      answer: 'Kami mendukung berbagai metode pembayaran seperti Transfer Bank (Virtual Account), E-Wallet (Gopay, OVO, Dana, ShopeePay), dan Kartu Kredit/Debit.'
    },
    {
      id: 5,
      category: 'pembayaran',
      question: 'Berapa lama proses penarikan dana (payout)?',
      answer: 'Penarikan dana dari dompet platform ke rekening bank lokal Anda umumnya diproses secara instan hingga maksimal 1x24 jam kerja.'
    },
    {
      id: 6,
      category: 'umum',
      question: 'Apa yang harus dilakukan jika terjadi perselisihan proyek?',
      answer: 'Jika hasil kerja tidak sesuai atau ada kendala komunikasi, Anda dapat membuka fitur "Pusat Resolusi" untuk meminta bantuan tim arbitrase kami sebagai penengah.'
    }
  ];

  function toggleFaq(id) {
    openFaq = openFaq === id ? null : id;
  }

  const filteredFaqs = $derived(faqs.filter(faq => {
    const matchesCategory = activeCategory === 'semua' || faq.category === activeCategory;
    const matchesSearch = faq.question.toLowerCase().includes(searchQuery.toLowerCase()) || 
                          faq.answer.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  }));
</script>

<svelte:head>
  <title>Pusat Bantuan & FAQ</title>
</svelte:head>

<section id="pusat-bantuan" class="help-page">
  <div class="help-grid" aria-hidden="true"></div>
  <div class="help-shell">
    <div class="help-intro">
      <div class="eyebrow"><span class="eyebrow-line"></span>Pusat bantuan <span class="eyebrow-count">{faqs.length} jawaban</span></div>
      <h1>Jawaban untuk<br /><em>langkah berikutmu.</em></h1>
      <p>Cari panduan singkat tentang cara kerja Kerjain, pembayaran, dan keamanan transaksi.</p>
    </div>

    <div class="search-box">
      <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg>
      <input type="search" bind:value={searchQuery} placeholder="Cari topik, misalnya escrow atau payout" aria-label="Cari pertanyaan bantuan" />
      {#if searchQuery}
        <button type="button" class="clear-search" on:click={() => searchQuery = ''} aria-label="Hapus pencarian">×</button>
      {/if}
    </div>

    <div class="category-row" aria-label="Filter kategori">
      {#each categories as cat}
        <button type="button" on:click={() => activeCategory = cat.id} class:active={activeCategory === cat.id}>
          {cat.label}
        </button>
      {/each}
    </div>

    <div class="result-heading">
      <span>Pertanyaan populer</span>
      <span class="result-count">{filteredFaqs.length.toString().padStart(2, '0')} ditemukan</span>
    </div>

    <div class="faq-list">
      {#if filteredFaqs.length > 0}
        {#each filteredFaqs as faq, index (faq.id)}
          <article class:expanded={openFaq === faq.id} class="faq-item">
            <button type="button" class="faq-trigger" on:click={() => toggleFaq(faq.id)} aria-expanded={openFaq === faq.id}>
              <span class="faq-number">0{index + 1}</span>
              <span class="faq-question">{faq.question}</span>
              <span class="faq-icon" aria-hidden="true">{openFaq === faq.id ? '−' : '+'}</span>
            </button>
            {#if openFaq === faq.id}
              <div transition:slide={{ duration: 200 }} class="faq-answer">{faq.answer}</div>
            {/if}
          </article>
        {/each}
      {:else}
        <div class="empty-state">
          <span class="empty-mark">?</span>
          <strong>Belum ada jawaban yang cocok.</strong>
          <p>Coba kata kunci lain atau lihat semua topik bantuan.</p>
          <button type="button" on:click={() => { searchQuery = ''; activeCategory = 'semua'; }}>Reset pencarian</button>
        </div>
      {/if}
    </div>

   <!-- Kartu Hubungi Support -->
<div class="bg-gradient-to-r from-slate-900 to-emerald-950/40 border border-slate-800 rounded-xl p-6 sm:p-8 mt-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
  <div>
    <h3 class="text-lg font-bold text-white mb-1">Belum Menemukan Jawaban?</h3>
    <p class="text-sm text-slate-400">Tim dukungan pelanggan kami siap membantu kendala Anda via WhatsApp.</p>
  </div>
  <a 
    href="https://wa.me/6287872594546?text=Halo%20Tim%20Support%20KERJAIN,%20saya%20butuh%20bantuan%20terkait..." 
    target="_blank"
    rel="noopener noreferrer"
    class="bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold px-5 py-2.5 rounded-lg text-xs transition-colors shrink-0 flex items-center gap-2"
  >
    <!-- Icon WhatsApp (Opsional) -->
    <svg class="w-4 h-4 fill-current" viewBox="0 0 50 24">
      <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
    </svg>
    Chat via WhatsApp
  </a>
</div>
</section>

<style>
  :global(body) { background: #07100f; }
  .help-page { position: relative; min-height: 100vh; overflow: hidden; color: #d8e6df; background: radial-gradient(circle at 78% 12%, rgba(39, 117, 91, .2), transparent 30%), #07100f; }
  .help-grid { position: absolute; inset: 0; opacity: .35; background-image: linear-gradient(rgba(166, 226, 201, .05) 1px, transparent 1px), linear-gradient(90deg, rgba(166, 226, 201, .05) 1px, transparent 1px); background-size: 56px 56px; mask-image: linear-gradient(to bottom, black, transparent 75%); }
  .help-shell { position: relative; width: min(100% - 2rem, 900px); margin: 0 auto; padding: 5rem 0 4rem; }
  .help-intro { max-width: 640px; }
  .eyebrow, .result-heading, .support-label { color: #77d5a9; font-size: .68rem; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
  .eyebrow { display: flex; align-items: center; gap: .65rem; }
  .eyebrow-line { width: 28px; height: 1px; background: #4dc995; }
  .eyebrow-count { color: #708b82; letter-spacing: .08em; }
  h1 { max-width: 620px; margin: 1.25rem 0 1rem; color: #f3fbf7; font-size: clamp(2.7rem, 7vw, 5.2rem); font-weight: 800; letter-spacing: -.055em; line-height: .95; }
  h1 em { color: #63d59e; font-style: normal; }
  .help-intro p { max-width: 470px; margin: 0; color: #8da79c; font-size: .98rem; line-height: 1.7; }
  .search-box { display: flex; align-items: center; gap: .8rem; margin-top: 2.75rem; padding: .95rem 1rem; border: 1px solid #263d35; border-radius: 8px; background: rgba(13, 30, 25, .88); box-shadow: 0 20px 60px rgba(0, 0, 0, .18); }
  .search-box:focus-within { border-color: #4dc995; box-shadow: 0 0 0 3px rgba(77, 201, 149, .1); }
  .search-box svg { width: 1.15rem; height: 1.15rem; flex: 0 0 auto; color: #5f8576; stroke-width: 1.8; }
  .search-box input { width: 100%; border: 0; outline: 0; color: #edf9f3; background: transparent; font: inherit; font-size: .9rem; }
  .search-box input::placeholder { color: #648076; }
  .clear-search { border: 0; color: #8fb6a7; background: transparent; cursor: pointer; font-size: 1.4rem; line-height: 1; }
  .category-row { display: flex; gap: .5rem; overflow-x: auto; margin: 1.25rem 0 3.5rem; padding-bottom: .25rem; scrollbar-width: none; }
  .category-row::-webkit-scrollbar { display: none; }
  .category-row button { flex: 0 0 auto; padding: .55rem .9rem; border: 1px solid #263d35; border-radius: 5px; color: #78948a; background: transparent; cursor: pointer; font: inherit; font-size: .75rem; font-weight: 700; transition: .2s ease; }
  .category-row button:hover, .category-row button.active { border-color: #63d59e; color: #06110d; background: #63d59e; }
  .result-heading { display: flex; justify-content: space-between; padding-bottom: .8rem; border-bottom: 1px solid #263d35; }
  .result-count { color: #638077; letter-spacing: .08em; }
  .faq-item { border-bottom: 1px solid #263d35; }
  .faq-trigger { display: grid; grid-template-columns: 2.5rem 1fr 1.5rem; align-items: center; gap: .75rem; width: 100%; padding: 1.45rem .2rem; border: 0; color: inherit; background: transparent; cursor: pointer; text-align: left; }
  .faq-trigger:hover .faq-question, .faq-item.expanded .faq-question { color: #63d59e; }
  .faq-number { color: #4f7465; font-size: .7rem; font-weight: 800; letter-spacing: .08em; }
  .faq-question { color: #e3f1eb; font-size: 1rem; font-weight: 650; transition: color .2s ease; }
  .faq-icon { justify-self: end; color: #63d59e; font-size: 1.25rem; font-weight: 300; }
  .faq-answer { max-width: 690px; margin: -.25rem 3.2rem 1.5rem; color: #8da79c; font-size: .9rem; line-height: 1.75; }
  .empty-state { padding: 3.5rem 1rem; border: 1px dashed #315247; text-align: center; }
  .empty-mark { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; margin: 0 auto 1rem; border: 1px solid #63d59e; border-radius: 50%; color: #63d59e; }
  .empty-state strong { color: #e3f1eb; }
  .empty-state p { margin: .5rem 0 1.25rem; color: #78948a; font-size: .85rem; }
  .empty-state button { border: 0; color: #63d59e; background: transparent; cursor: pointer; font: inherit; font-size: .8rem; font-weight: 700; }
  .support-band { display: flex; align-items: end; justify-content: space-between; gap: 2rem; margin-top: 4.5rem; padding: 2rem 0 0; border-top: 1px solid #3d6556; }
  .support-band h2 { margin: .55rem 0 0; color: #f0faf5; font-size: clamp(1.5rem, 4vw, 2.2rem); letter-spacing: -.04em; line-height: 1.05; }
  .support-band a { display: inline-flex; align-items: center; gap: .7rem; padding: .85rem 1rem; border: 1px solid #63d59e; color: #07100f; background: #63d59e; font-size: .78rem; font-weight: 800; text-decoration: none; transition: .2s ease; }
  .support-band a:hover { background: #a1edc7; transform: translateY(-2px); }
  @media (max-width: 560px) { .help-shell { padding-top: 3.5rem; } .category-row { margin-bottom: 2.75rem; } .faq-trigger { grid-template-columns: 2rem 1fr 1rem; padding: 1.2rem 0; } .faq-question { font-size: .9rem; line-height: 1.45; } .faq-answer { margin-left: 2.75rem; margin-right: 1rem; } .support-band { align-items: start; flex-direction: column; } .support-band a { width: 100%; justify-content: space-between; } }
</style>