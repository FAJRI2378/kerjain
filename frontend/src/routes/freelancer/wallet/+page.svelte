<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  // State Dompet & Riwayat Saldo
  let saldo = $state(0);
  let withdrawAmount = $state('');
  let selectedBank = $state('BCA');
  let accountNumber = $state('');
  let isWithdrawing = $state(false);

  // State Filter Rekap Transaksi
  let selectedMonth = $state('all');
  let selectedYear = $state('2026');
  let txSearchQuery = $state('');

  // State Pagination
  let currentPage = $state(1);
  let itemsPerPage = $state(3);

  // Data Dummy
  let dummyTransactions = [
    { id: 1, type: 'in', title: 'Pembayaran Tugas: Desain Logo Kopi', amount: 300000, date: '2026-09-04', month: '09', year: '2026' },
    { id: 2, type: 'out', title: 'Penarikan Dana ke BCA (***8821)', amount: 1500000, date: '2026-09-01', month: '09', year: '2026' },
    { id: 3, type: 'in', title: 'Pembayaran Tugas: Admin Medsos', amount: 1200000, date: '2026-08-28', month: '08', year: '2026' },
    { id: 4, type: 'in', title: 'Pembayaran Tugas: Input Data Tokopedia', amount: 150000, date: '2026-08-15', month: '08', year: '2026' },
    { id: 5, type: 'out', title: 'Penarikan Dana ke DANA (0812***)', amount: 500000, date: '2026-07-20', month: '07', year: '2026' },
    { id: 6, type: 'in', title: 'Pembayaran Tugas: Pembuatan Landing Page', amount: 2500000, date: '2026-07-10', month: '07', year: '2026' }
  ];
  let transactions = $state([]);

  async function loadWallet() {
    try {
      const res = await api.get('/api/freelancer/wallet');
      saldo = res.data.balance;
      transactions = res.data.transactions ?? [];
    } catch (err) {
      saldo = 4500000;
      transactions = dummyTransactions;
    }
  }

  onMount(loadWallet);

  let notifications = $state([
    { id: 1, icon: '💰', title: 'Dana Masuk!', desc: 'Pembayaran Rp 300.000 untuk tugas Desain Logo telah masuk ke dompet.', time: 'Kemarin, 14:20', unread: true },
    { id: 2, icon: '⭐', title: 'Ulasan Baru!', desc: 'Kopi Kenangan Senja memberikan rating 5 bintang pada profil Anda.', time: '2 hari lalu', unread: false },
    { id: 3, icon: '✅', title: 'Lamaran Diterima', desc: 'UMKM Toko Baju Nabila menerima Anda untuk tugas Admin Instagram.', time: '3 hari lalu', unread: false }
  ]);

  function formatRupiah(number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
  }

  let filteredTransactions = $derived(
    transactions.filter(tx => {
      const matchMonth = selectedMonth === 'all' || tx.month === selectedMonth;
      const matchYear = selectedYear === 'all' || tx.year === selectedYear;
      const matchSearch = !txSearchQuery || tx.title.toLowerCase().includes(txSearchQuery.toLowerCase());
      return matchMonth && matchYear && matchSearch;
    })
  );

  $effect(() => {
    selectedMonth;
    selectedYear;
    txSearchQuery;
    currentPage = 1;
  });

  let totalPemasukan = $derived(filteredTransactions.filter(tx => tx.type === 'in').reduce((sum, tx) => sum + tx.amount, 0));
  let totalPenarikan = $derived(filteredTransactions.filter(tx => tx.type === 'out').reduce((sum, tx) => sum + tx.amount, 0));
  let totalPages = $derived(Math.ceil(filteredTransactions.length / itemsPerPage) || 1);
  let paginatedTransactions = $derived(filteredTransactions.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage));

  function goToPage(page) {
    if (page >= 1 && page <= totalPages) {
      currentPage = page;
    }
  }

  async function handleWithdraw(e) {
    e.preventDefault();
    const amountNum = Number(withdrawAmount);

    if (!amountNum || amountNum <= 0) {
      toast('Masukkan nominal penarikan yang valid.', 'error');
      return;
    }
    if (amountNum > saldo) {
      toast('Saldo dompet tidak mencukupi untuk penarikan ini.', 'error');
      return;
    }
    if (!accountNumber.trim()) {
      toast('Masukkan nomor rekening / e-wallet tujuan.', 'error');
      return;
    }

    isWithdrawing = true;
    try {
      const res = await api.post('/api/freelancer/wallet/withdraw', { amount: amountNum, bank_name: selectedBank, account_number: accountNumber });
      saldo = res.data.balance;
      transactions = res.data.transactions ?? [];
      withdrawAmount = '';
      accountNumber = '';
      toast('Penarikan dana berhasil diproses!', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Penarikan dana gagal.'), 'error');
    } finally {
      isWithdrawing = false;
    }
  }

  function markAllRead() {
    notifications = notifications.map(n => ({ ...n, unread: false }));
    toast('Semua notifikasi ditandai dibaca', 'success');
  }
</script>

<div class="wallet-page font-sans">
  <div class="page-header">
    <div>
      <h1 class="page-title">Dompet & Pusat Aktivitas 💳</h1>
      <p class="page-sub">Kelola pendapatan Anda, rekap keuangan bulanan, dan pantau aktivitas platform.</p>
    </div>
  </div>

  <div class="wallet-grid">
    <div class="wallet-left">
      <div class="card balance-card gradient-bg">
        <p class="balance-label">Total Saldo Tersedia</p>
        <h2 class="balance-value">{formatRupiah(saldo)}</h2>
        <div class="balance-badge">🟢 Siap Ditarik (Instant)</div>
      </div>

      <div class="card withdraw-card">
        <h3 class="card-section-title">💸 Tarik Dana (Withdrawal)</h3>
        <form onsubmit={handleWithdraw} class="withdraw-form">
          <div class="form-group">
            <label for="amount">Nominal Penarikan (Rp)</label>
            <input id="amount" type="number" bind:value={withdrawAmount} placeholder="Contoh: 500000" class="form-input" required />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="bank">Metode Bank / E-Wallet</label>
              <select id="bank" bind:value={selectedBank} class="form-input">
                <option value="BCA">BCA</option>
                <option value="Mandiri">Mandiri</option>
                <option value="GoPay">GoPay</option>
                <option value="OVO">OVO</option>
                <option value="DANA">DANA</option>
              </select>
            </div>
            <div class="form-group">
              <label for="acc">Nomor Rekening / No. HP</label>
              <input id="acc" type="text" bind:value={accountNumber} placeholder="Nomor akun tujuan" class="form-input" required />
            </div>
          </div>
          <button type="submit" class="btn-withdraw" disabled={isWithdrawing}>
            {isWithdrawing ? 'Memproses Penarikan...' : 'Tarik Saldo Sekarang'}
          </button>
        </form>
      </div>

      <div class="card transaction-card">
        <div class="tx-header-wrap">
          <h3 class="card-section-title" style="margin: 0;">📜 Riwayat & Rekap</h3>
        </div>

        <div class="rekap-filter-panel">
          <div class="filter-controls-row">
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

          <div class="rekap-summary-grid">
            <div class="rekap-box box-in">
              <span class="rekap-label">Total Pemasukan</span>
              <span class="rekap-val text-green">+{formatRupiah(totalPemasukan)}</span>
            </div>
            <div class="rekap-box box-out">
              <span class="rekap-label">Total Penarikan</span>
              <span class="rekap-val text-red">-{formatRupiah(totalPenarikan)}</span>
            </div>
          </div>
        </div>

        <div class="tx-list">
          {#each paginatedTransactions as tx (tx.id)}
            <div class="tx-item">
              <div class="tx-icon {tx.type === 'in' ? 'tx-in' : 'tx-out'}">
                {tx.type === 'in' ? '📥' : '📤'}
              </div>
              <div class="tx-info">
                <h4 class="tx-title">{tx.title}</h4>
                <p class="tx-date">Tanggal: {tx.date}</p>
              </div>
              <span class="tx-amount {tx.type === 'in' ? 'text-green' : 'text-slate'}">
                {tx.type === 'in' ? '+' : '-'}{formatRupiah(tx.amount)}
              </span>
            </div>
          {:else}
            <div class="empty-tx">
              <p>Tidak ada riwayat transaksi pada periode yang dipilih.</p>
            </div>
          {/each}
        </div>

        {#if filteredTransactions.length > 0}
          <div class="pagination-container">
            <span class="pagination-info">
              Menampilkan {(currentPage - 1) * itemsPerPage + 1} - {Math.min(currentPage * itemsPerPage, filteredTransactions.length)} dari {filteredTransactions.length} transaksi
            </span>
            <div class="pagination-buttons">
              <button class="btn-page" onclick={() => goToPage(currentPage - 1)} disabled={currentPage === 1}>‹ Prev</button>
              {#each Array(totalPages) as _, i}
                <button class="btn-page {currentPage === i + 1 ? 'active-page' : ''}" onclick={() => goToPage(i + 1)}>{i + 1}</button>
              {/each}
              <button class="btn-page" onclick={() => goToPage(currentPage + 1)} disabled={currentPage === totalPages}>Next ›</button>
            </div>
          </div>
        {/if}
      </div>
    </div>

    <div class="wallet-right">
      <div class="card notification-panel">
        <div class="notif-header">
          <h3 class="card-section-title">🔔 Log Aktivitas</h3>
          <button class="btn-mark-read" onclick={markAllRead}>Tandai Dibaca</button>
        </div>

        <div class="notif-list">
          {#each notifications as notif (notif.id)}
            <div class="notif-item {notif.unread ? 'unread' : ''}">
              <span class="notif-icon">{notif.icon}</span>
              <div class="notif-content">
                <div class="notif-row">
                  <h4 class="notif-title">{notif.title}</h4>
                  <span class="notif-time">{notif.time}</span>
                </div>
                <p class="notif-desc">{notif.desc}</p>
              </div>
            </div>
          {/each}
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Base & Layout */
  .wallet-page {
    padding: 16px; /* Reduced for mobile */
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 16px; /* Reduced for mobile */
  }

  .wallet-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr); /* Single column on mobile & tablet */
    gap: 16px;
  }

  .wallet-left, .wallet-right {
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-width: 0;
  }

  /* Breakpoint: Desktop */
  @media (min-width: 992px) {
    .wallet-page {
      padding: 32px 24px;
      gap: 28px;
    }
    .wallet-grid {
      grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
      gap: 24px;
    }
    .wallet-left, .wallet-right {
      gap: 24px;
    }
  }

  /* Cards & Headers */
  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px; /* Reduced padding for mobile */
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  /* Breakpoint: Tablet & Up */
  @media (min-width: 768px) {
    .page-header {
      padding: 28px 32px;
    }
    .card {
      padding: 24px;
    }
  }

  .page-title {
    font-size: 20px; /* Smaller font on mobile */
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  @media (min-width: 768px) {
    .page-title { font-size: 24px; }
  }

  .page-sub {
    color: #64748b;
    font-size: 13.5px;
    margin: 0;
  }

  .gradient-bg {
    background: linear-gradient(135deg, #0d233a 0%, #1a365d 100%);
    color: white;
    border: none;
    min-width: 0;
  }

  .balance-label { font-size: 13px; color: #94a3b8; margin: 0 0 4px; }
  .balance-value { font-size: 28px; font-weight: 800; margin: 0 0 12px; overflow-wrap: anywhere; }
  
  @media (min-width: 768px) {
    .balance-value { font-size: 32px; }
  }

  .balance-badge {
    display: inline-block;
    background: rgba(16,185,129,0.2);
    color: #34d399;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 6px;
  }

  .card-section-title {
    font-size: 16px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 16px;
  }

  /* Forms */
  .withdraw-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr; /* Stack vertically on mobile */
    gap: 12px;
  }

  @media (min-width: 640px) {
    .form-row { grid-template-columns: 1fr 1fr; } /* Side-by-side on larger screens */
  }

  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group label { font-size: 12px; font-weight: 700; color: #334155; }
  .form-input {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    outline: none;
    background: #ffffff;
    color: #0f172a;
    transition: border-color 0.2s, background-color 0.3s ease;
  }
  .form-input:focus { border-color: #15803d; }

  .btn-withdraw {
    background: #15803d;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn-withdraw:hover:not(:disabled) { background: #166534; }
  .btn-withdraw:disabled { opacity: 0.6; cursor: not-allowed; }

  /* Rekap & Filter */
  .rekap-filter-panel {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
    background: #f8fafc;
    padding: 12px; /* Smaller on mobile */
    border-radius: 12px;
    border: 1px solid #f1f5f9;
  }

  @media (min-width: 768px) {
    .rekap-filter-panel { padding: 16px; }
  }

  .filter-controls-row {
    display: grid;
    grid-template-columns: 1fr; /* Stack on mobile */
    gap: 8px;
  }

  @media (min-width: 640px) {
    .filter-controls-row { grid-template-columns: 1fr 1fr; } /* Two columns on tablet */
  }

  .rekap-summary-grid {
    display: grid;
    grid-template-columns: 1fr; /* Stack vertically on very small screens */
    gap: 10px;
  }

  @media (min-width: 480px) {
    .rekap-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } /* Side-by-side above 480px */
  }

  .rekap-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
  }

  .rekap-label { font-size: 11px; font-weight: 700; color: #64748b; }
  .rekap-val { font-size: 15px; font-weight: 800; min-width: 0; overflow-wrap: anywhere; }

  /* Transaksi */
  .tx-header-wrap {
    margin-bottom: 16px;
  }

  .tx-list { display: flex; flex-direction: column; gap: 12px; }
  .tx-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    flex-wrap: wrap; /* Allow wrapping on very small screens */
  }

  .tx-icon {
    width: 36px; height: 36px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; flex-shrink: 0;
  }

  .tx-in { background: #dcfce7; }
  .tx-out { background: #fee2e2; }

  .tx-info { flex: 1; overflow: hidden; min-width: 150px; }
  .tx-title { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .tx-date { margin: 0; font-size: 11px; color: #64748b; }
  .tx-amount { font-size: 13px; font-weight: 800; white-space: nowrap; }
  
  .text-green { color: #15803d; }
  .text-red { color: #dc2626; }
  .text-slate { color: #475569; }
  .empty-tx { text-align: center; padding: 24px; color: #64748b; font-size: 13px; }

  /* Pagination */
  .pagination-container {
    display: flex;
    flex-direction: column; /* Stack on mobile */
    align-items: center;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #f1f5f9;
  }

  @media (min-width: 640px) {
    .pagination-container {
      flex-direction: row; /* Horizontal on larger screens */
      justify-content: space-between;
    }
  }

  .pagination-info { font-size: 12px; color: #64748b; text-align: center; }
  .pagination-buttons { display: flex; gap: 4px; flex-wrap: wrap; justify-content: center; }

  .btn-page {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
  }
  .btn-page:hover:not(:disabled) { background: #f1f5f9; color: #0f172a; }
  .btn-page.active-page { background: #15803d; color: white; border-color: #15803d; }
  .btn-page:disabled { opacity: 0.5; cursor: not-allowed; }

  /* Notifikasi */
  .notif-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap; /* Prevent overlap on small mobile screens */
    gap: 8px;
  }

  .btn-mark-read {
    background: none; border: none; font-size: 11px; font-weight: 700;
    color: #2563eb; cursor: pointer; padding: 0;
  }

  .notif-list { display: flex; flex-direction: column; gap: 12px; }
  .notif-item {
    display: flex; gap: 12px; padding: 14px;
    background: #f8fafc; border-radius: 10px; border: 1px solid #f1f5f9;
  }
  .notif-item.unread { background: #f0fdf4; border-color: #bbf7d0; }
  .notif-icon { font-size: 20px; flex-shrink: 0; }
  .notif-content { flex: 1; }
  
  .notif-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 4px; flex-wrap: wrap; gap: 4px;
  }
  
  .notif-title { margin: 0; font-size: 13px; font-weight: 800; color: #0f172a; }
  .notif-time { font-size: 10px; color: #94a3b8; white-space: nowrap; }
  .notif-desc { margin: 0; font-size: 12px; color: #475569; line-height: 1.4; }

  /* Dark Mode Support (Tetap Sama) */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .card:not(.gradient-bg)) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .page-title),
  :global(body.dark-theme .card-section-title),
  :global(body.dark-theme .tx-title),
  :global(body.dark-theme .notif-title) { color: #ffffff !important; }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .tx-date),
  :global(body.dark-theme .notif-time),
  :global(body.dark-theme .notif-desc),
  :global(body.dark-theme .rekap-label),
  :global(body.dark-theme .pagination-info) { color: #94a3b8 !important; }
  :global(body.dark-theme .form-group label) { color: #cbd5e1 !important; }
  :global(body.dark-theme .form-input) { background-color: #0f172a !important; border-color: #334155 !important; color: #ffffff !important; }
  :global(body.dark-theme .rekap-filter-panel) { background-color: #0f172a !important; border-color: #334155 !important; }
  :global(body.dark-theme .rekap-box) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .tx-item),
  :global(body.dark-theme .notif-item) { background-color: #0f172a !important; border-color: #334155 !important; }
  :global(body.dark-theme .notif-item.unread) { background-color: rgba(21, 128, 61, 0.15) !important; border-color: #15803d !important; }
  :global(body.dark-theme .btn-page) { background-color: #0f172a !important; border-color: #334155 !important; color: #cbd5e1 !important; }
  :global(body.dark-theme .btn-page:hover:not(:disabled)) { background-color: #334155 !important; color: #ffffff !important; }
  :global(body.dark-theme .btn-page.active-page) { background-color: #15803d !important; border-color: #15803d !important; color: #ffffff !important; }
</style>