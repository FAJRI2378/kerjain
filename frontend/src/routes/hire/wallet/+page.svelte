<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  // State Dompet Escrow UMKM
  let escrowBalance = $state(0);
  let topUpAmount = $state('');
  let selectedBank = $state('BCA');
  let isTopUpModalOpen = $state(false);
  let isProcessing = $state(false);

  // State Filter Rekap Transaksi
  let selectedMonth = $state('all');
  let selectedYear = $state('2026');
  let txSearchQuery = $state('');

  // State Pagination
  let currentPage = $state(1);
  let itemsPerPage = $state(4);

  // Data Dummy sebagai fallback saat backend tidak aktif
  let dummyTransactions = [
    { id: 1, type: 'topup', title: 'Top Up Escrow via Transfer BCA', amount: 5000000, date: '2026-09-02', month: '09', year: '2026' },
    { id: 2, type: 'out', title: 'Bayar Kontrak: Desain Logo Kopi Senja', amount: 350000, date: '2026-09-04', month: '09', year: '2026' },
    { id: 3, type: 'out', title: 'Bayar Kontrak: Admin Medsos Instagram', amount: 1200000, date: '2026-08-28', month: '08', year: '2026' },
    { id: 4, type: 'topup', title: 'Top Up Escrow via QRIS Mandiri', amount: 3000000, date: '2026-08-20', month: '08', year: '2026' },
    { id: 5, type: 'out', title: 'Bayar Kontrak: Input Data Tokopedia', amount: 150000, date: '2026-08-15', month: '08', year: '2026' }
  ];
  let transactions = $state([]);

  async function loadWallet() {
    try {
      const res = await api.get('/api/hire/wallet');
      escrowBalance = res.data.balance;
      transactions = res.data.transactions ?? [];
    } catch (err) {
      // Data dummy untuk presentasi jika backend tidak aktif
      escrowBalance = 7500000;
      transactions = dummyTransactions;
    }
  }

  onMount(loadWallet);

  // Filter reaktif untuk Riwayat Transaksi
  let filteredTransactions = $derived(
    transactions.filter(tx => {
      const matchMonth = selectedMonth === 'all' || tx.month === selectedMonth;
      const matchYear = selectedYear === 'all' || tx.year === selectedYear;
      const matchSearch = !txSearchQuery || tx.title.toLowerCase().includes(txSearchQuery.toLowerCase());
      return matchMonth && matchYear && matchSearch;
    })
  );

  // Reset halaman ke 1 saat filter berubah
  $effect(() => {
    selectedMonth;
    selectedYear;
    txSearchQuery;
    currentPage = 1;
  });

  // Hitung Rekap Total dari hasil filter
  let totalTopUp = $derived(
    filteredTransactions.filter(tx => tx.type === 'topup').reduce((sum, tx) => sum + tx.amount, 0)
  );

  let totalPengeluaran = $derived(
    filteredTransactions.filter(tx => tx.type === 'out').reduce((sum, tx) => sum + tx.amount, 0)
  );

  // Logika Pagination
  let totalPages = $derived(Math.ceil(filteredTransactions.length / itemsPerPage) || 1);

  let paginatedTransactions = $derived(
    filteredTransactions.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage)
  );

  function goToPage(page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
    }
  }

  // Top Up Saldo Escrow terhubung ke API
  async function handleTopUp(e) {
    e.preventDefault();
    const amountNum = Number(topUpAmount);

    if (!amountNum || amountNum <= 0) {
      toast('Masukkan nominal top up yang valid.', 'error');
      return;
    }

    isProcessing = true;
    try {
      const res = await api.post('/api/hire/wallet/topup', {
        amount: amountNum,
        bank_name: selectedBank
      });
      escrowBalance = res.data.balance;
      transactions = res.data.transactions ?? [];
      isProcessing = false;
      topUpAmount = '';
      isTopUpModalOpen = false;
      toast('Top Up saldo Escrow berhasil!', 'success');
    } catch (err) {
      isProcessing = false;
      toast(errorMessage(err, 'Top up gagal.'), 'error');
    }
  }
</script>

<div class="wallet-page font-sans">
  <div class="page-header">
    <div>
      <h1 class="page-title">Dompet Escrow & Keuangan 🛡️</h1>
      <p class="page-sub">Kelola dana jaminan proyek, lakukan top up saldo, dan pantau rekap pengeluaran bisnis.</p>
    </div>
  </div>

  <div class="wallet-grid">
    <!-- Kolom Kiri: Kartu Saldo & Tombol Top Up -->
    <div class="wallet-left">
      <div class="card balance-card gradient-bg">
        <p class="balance-label">Total Saldo Escrow Tersedia</p>
        <h2 class="balance-value">{formatRupiah(escrowBalance)}</h2>
        <div class="balance-info-row">
          <span class="balance-badge">🛡️ Aman Terjamin</span>
          <button class="btn-open-topup" onclick={() => isTopUpModalOpen = true}>+ Top Up Saldo</button>
        </div>
      </div>

      <!-- Info Keamanan Escrow -->
      <div class="card info-card">
        <h3 class="card-section-title">💡 Sistem Escrow (Rekber)</h3>
        <p class="info-desc">
          Dana Anda aman di dalam sistem penampungan Kerjain. Saldo hanya akan diteruskan ke freelancer ketika Anda menekan tombol <strong>"Approve & Cairkan"</strong> setelah tugas selesai dikerjakan.
        </p>
      </div>
    </div>

    <!-- Kolom Kanan / Bawah: Riwayat & Rekap Keuangan -->
    <div class="wallet-right">
      <div class="card transaction-card">
        <div class="tx-header-wrap">
          <h3 class="card-section-title" style="margin: 0;">📜 Riwayat & Rekap Pengeluaran</h3>
        </div>

        <!-- Filter & Rekap Panel -->
        <div class="rekap-filter-panel">
          <div class="filter-controls-row">
            <input 
              type="text" 
              bind:value={txSearchQuery} 
              placeholder="Cari riwayat transaksi..." 
              class="form-input search-tx-input" 
            />
            <select bind:value={selectedMonth} class="form-input select-filter">
              <option value="all">Semua Bulan</option>
              <option value="01">Januari</option>
              <option value="02">Februari</option>
              <option value="03">Maret</option>
              <option value="04">April</option>
              <option value="05">Mei</option>
              <option value="06">Juni</option>
              <option value="07">Juli</option>
              <option value="08">Agustus</option>
              <option value="09">September</option>
              <option value="10">Oktober</option>
              <option value="11">November</option>
              <option value="12">Desember</option>
            </select>
            <select bind:value={selectedYear} class="form-input select-filter">
              <option value="all">Semua Tahun</option>
              <option value="2026">2026</option>
              <option value="2025">2025</option>
            </select>
          </div>

          <!-- Kotak Ringkasan Rekap -->
          <div class="rekap-summary-grid">
            <div class="rekap-box">
              <span class="rekap-label">Total Top Up</span>
              <span class="rekap-val text-green">+{formatRupiah(totalTopUp)}</span>
            </div>
            <div class="rekap-box">
              <span class="rekap-label">Total Pengeluaran Proyek</span>
              <span class="rekap-val text-red">-{formatRupiah(totalPengeluaran)}</span>
            </div>
          </div>
        </div>

        <div class="tx-list">
          {#each paginatedTransactions as tx (tx.id)}
            <div class="tx-item">
              <div class="tx-icon {tx.type === 'topup' ? 'tx-in' : 'tx-out'}">
                {tx.type === 'topup' ? '💳' : '📤'}
              </div>
              <div class="tx-info">
                <h4 class="tx-title">{tx.title}</h4>
                <p class="tx-date">Tanggal: {tx.date}</p>
              </div>
              <span class="tx-amount {tx.type === 'topup' ? 'text-green' : 'text-slate'}">
                {tx.type === 'topup' ? '+' : '-'}{formatRupiah(tx.amount)}
              </span>
            </div>
          {:else}
            <div class="empty-tx">
              <p>Tidak ada riwayat transaksi pada periode yang dipilih.</p>
            </div>
          {/each}
        </div>

        <!-- Kontrol Pagination -->
        {#if filteredTransactions.length > 0}
          <div class="pagination-container">
            <span class="pagination-info">
              Menampilkan {(currentPage - 1) * itemsPerPage + 1} - {Math.min(currentPage * itemsPerPage, filteredTransactions.length)} dari {filteredTransactions.length} transaksi
            </span>
            <div class="pagination-buttons">
              <button 
                class="btn-page" 
                onclick={() => goToPage(currentPage - 1)} 
                disabled={currentPage === 1}
              >
                ‹ Prev
              </button>
              
              {#each Array(totalPages) as _, i}
                <button 
                  class="btn-page {currentPage === i + 1 ? 'active-page' : ''}" 
                  onclick={() => goToPage(i + 1)}
                >
                  {i + 1}
                </button>
              {/each}

              <button 
                class="btn-page" 
                onclick={() => goToPage(currentPage + 1)} 
                disabled={currentPage === totalPages}
              >
                Next ›
              </button>
            </div>
          </div>
        {/if}
      </div>
    </div>
  </div>
</div>

<!-- Modal Top Up Saldo Escrow -->
{#if isTopUpModalOpen}
  <div class="modal-backdrop" onclick={() => isTopUpModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header">
        <div>
          <h2 class="modal-title">Top Up Saldo Escrow 💳</h2>
          <p class="modal-sub">Tambahkan dana jaminan untuk mulai menayangkan tugas.</p>
        </div>
        <button class="btn-close" onclick={() => isTopUpModalOpen = false}>✕</button>
      </div>

      <form onsubmit={handleTopUp} class="modal-form">
        <div class="form-group">
          <label for="topup-amt">Nominal Top Up (Rp)</label>
          <input 
            id="topup-amt" 
            type="number" 
            min="50000" 
            step="50000" 
            bind:value={topUpAmount} 
            placeholder="Contoh: 1000000" 
            required 
            class="form-input" 
          />
        </div>

        <div class="form-group">
          <label for="topup-bank">Metode Pembayaran</label>
          <select id="topup-bank" bind:value={selectedBank} class="form-input">
            <option value="BCA">Transfer Bank BCA (Virtual Account)</option>
            <option value="Mandiri">Transfer Bank Mandiri</option>
            <option value="QRIS">QRIS (Instan & Otomatis)</option>
            <option value="GoPay">GoPay / OVO</option>
          </select>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick={() => isTopUpModalOpen = false}>Batal</button>
          <button type="submit" disabled={isProcessing} class="btn-save">
            {isProcessing ? 'Memproses...' : 'Lanjutkan Pembayaran'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  .wallet-page { max-width: 1200px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px; }
  .page-header { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
  .page-title { font-size: 24px; font-weight: 800; color: #0d233a; margin: 0 0 4px; }
  .page-sub { color: #64748b; font-size: 13.5px; margin: 0; }

  .card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
  .wallet-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; }
  @media (min-width: 992px) { .wallet-grid { grid-template-columns: minmax(0, 1fr) minmax(0, 1.5fr); } }
  .wallet-left, .wallet-right { display: flex; flex-direction: column; gap: 24px; min-width: 0; }

  .gradient-bg { background: linear-gradient(135deg, #0d233a 0%, #1a365d 100%); color: white; border: none; min-width: 0; }
  .balance-label { font-size: 13px; color: #94a3b8; margin: 0 0 4px; }
  .balance-value { font-size: clamp(24px, 7vw, 32px); font-weight: 800; margin: 0 0 16px; overflow-wrap: anywhere; }
  .balance-info-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; }
  .balance-badge { background: rgba(16,185,129,0.2); color: #34d399; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
  .btn-open-topup { background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; transition: background 0.2s; }
  .btn-open-topup:hover { background: #4338ca; }

  .card-section-title { font-size: 16px; font-weight: 800; color: #0d233a; margin: 0 0 12px; }
  .info-desc { font-size: 13px; color: #64748b; line-height: 1.5; margin: 0; }

  /* Rekap & Filter Keuangan */
  .rekap-filter-panel { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #f1f5f9; }
  .filter-controls-row { display: grid; grid-template-columns: 1fr; gap: 8px; }
  @media (min-width: 768px) { .filter-controls-row { grid-template-columns: 2fr 1fr 1fr; } }

  .form-input { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; outline: none; background: #ffffff; color: #0f172a; }
  .form-input:focus { border-color: #15803d; }

  .rekap-summary-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
  .rekap-box { background: #ffffff; border: 1px solid #e2e8f0; padding: 10px 14px; border-radius: 8px; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
  .rekap-label { font-size: 11px; font-weight: 700; color: #64748b; }
  .rekap-val { font-size: 15px; font-weight: 800; min-width: 0; overflow-wrap: anywhere; }

  /* Transaksi */
  .tx-list { display: flex; flex-direction: column; gap: 12px; }
  .tx-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9; }
  .tx-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
  .tx-in { background: #dcfce7; } .tx-out { background: #fee2e2; }
  .tx-info { flex: 1; overflow: hidden; }
  .tx-title { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .tx-date { margin: 0; font-size: 11px; color: #64748b; }
  .tx-amount { font-size: 13px; font-weight: 800; }
  .text-green { color: #15803d; } .text-red { color: #dc2626; } .text-slate { color: #475569; }
  .empty-tx { text-align: center; padding: 24px; color: #64748b; font-size: 13px; }

  /* Pagination */
  .pagination-container { display: flex; flex-direction: column; align-items: center; gap: 12px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
  @media (min-width: 640px) { .pagination-container { flex-direction: row; justify-content: space-between; } }
  .pagination-info { font-size: 12px; color: #64748b; }
  .pagination-buttons { display: flex; gap: 4px; }
  .btn-page { background: #ffffff; border: 1px solid #cbd5e1; color: #475569; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; }
  .btn-page:hover:not(:disabled) { background: #f1f5f9; color: #0f172a; }
  .btn-page.active-page { background: #15803d; color: white; border-color: #15803d; }
  .btn-page:disabled { opacity: 0.5; cursor: not-allowed; }

  /* Modal */
  .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 100; }
  .modal-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 440px; padding: 24px; }
  .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
  .modal-title { font-size: 18px; font-weight: 800; color: #ffffff; margin: 0 0 2px; }
  .modal-sub { font-size: 12.5px; color: #94a3b8; margin: 0; }
  .btn-close { background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer; }
  .modal-form { display: flex; flex-direction: column; gap: 16px; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group label { font-size: 12px; font-weight: 700; color: #cbd5e1; }
  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; border-top: 1px solid #334155; padding-top: 16px; }
  .btn-cancel { background: #334155; border: none; color: #f8fafc; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
  .btn-save { background: #4f46e5; color: white; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }

  /* Dark Mode */
  :global(body.dark-theme .page-header), :global(body.dark-theme .card:not(.gradient-bg)) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .page-title), :global(body.dark-theme .card-section-title), :global(body.dark-theme .tx-title) { color: #ffffff !important; }
  :global(body.dark-theme .page-sub), :global(body.dark-theme .info-desc), :global(body.dark-theme .tx-date), :global(body.dark-theme .rekap-label) { color: #94a3b8 !important; }
  :global(body.dark-theme .rekap-filter-panel), :global(body.dark-theme .tx-item), :global(body.dark-theme .rekap-box) { background-color: #0f172a !important; border-color: #334155 !important; }
  :global(body.dark-theme .form-input) { background-color: #0f172a !important; border-color: #334155 !important; color: #ffffff !important; }
  :global(body.dark-theme .btn-page) { background-color: #0f172a !important; border-color: #334155 !important; color: #cbd5e1 !important; }
</style>