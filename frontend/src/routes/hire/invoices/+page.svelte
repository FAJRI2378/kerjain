<script>
  import { onMount } from 'svelte';
  import { formatRupiah } from '$lib/format.js';
  import { toast } from '$lib/ui/toast.svelte.js';

  // Data Dummy Invoice & Rekap Pengeluaran Escrow
  let invoices = $state([
    { id: 'INV-2026-001', task: 'Desain Logo UMKM Kopi Senja', worker: 'Ahmad Rizki', amount: 350000, date: '04 Sep 2026', status: 'Lunas (Escrow Released)' },
    { id: 'INV-2026-002', task: 'Admin Medsos Instagram', worker: 'Siti Aminah', amount: 1200000, date: '28 Agu 2026', status: 'Lunas (Escrow Released)' },
    { id: 'INV-2026-003', task: 'Input Data Tokopedia', worker: 'Budi Santoso', amount: 150000, date: '15 Agu 2026', status: 'Lunas (Escrow Released)' }
  ]);

  let totalSpent = $derived(invoices.reduce((sum, inv) => sum + inv.amount, 0));

  function downloadInvoice(invId) {
    toast(`Mengunduh kuitansi resmi ${invId} dalam format PDF...`, 'success');
  }
</script>

<div class="invoices-page font-sans">
  <div class="page-header">
    <div>
      <h1 class="page-title">Invoice & Kuitansi Pembayaran 🧾</h1>
      <p class="page-sub">Unduh bukti transaksi resmi dan pantau seluruh pengeluaran dana proyek bisnis Anda.</p>
    </div>
    <div class="summary-box-header">
      <span class="sum-lbl">Total Pengeluaran</span>
      <span class="sum-val">{formatRupiah(totalSpent)}</span>
    </div>
  </div>

  <div class="card invoice-card-container">
    <h3 class="section-title">Daftar Riwayat Invoice</h3>
    <div class="invoice-list">
      {#each invoices as inv (inv.id)}
        <div class="invoice-item">
          <div class="inv-info">
            <span class="inv-id">{inv.id}</span>
            <h4 class="inv-task">{inv.task}</h4>
            <p class="inv-meta">Freelancer: <strong>{inv.worker}</strong> • Tanggal: {inv.date}</p>
          </div>

          <div class="inv-action-col">
            <div class="inv-amount-box">
              <span class="inv-amount">{formatRupiah(inv.amount)}</span>
              <span class="inv-status">✅ {inv.status}</span>
            </div>
            <button onclick={() => downloadInvoice(inv.id)} class="btn-download">
              📥 Unduh PDF
            </button>
          </div>
        </div>
      {/each}
    </div>
  </div>
</div>

<style>
  .invoices-page { max-width: 1000px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px; }
  .page-header { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px 32px; display: flex; flex-direction: column; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
  @media (min-width: 640px) { .page-header { flex-direction: row; align-items: center; justify-content: space-between; } }
  .page-title { font-size: 24px; font-weight: 800; color: #0d233a; margin: 0 0 4px; }
  .page-sub { font-size: 13.5px; color: #64748b; margin: 0; }
  
  .summary-box-header { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 20px; border-radius: 12px; text-align: right; }
  .sum-lbl { display: block; font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; }
  .sum-val { font-size: 18px; font-weight: 800; color: #15803d; }

  .card { background: #ffffff; border-radius: 16px; padding: 28px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
  .section-title { font-size: 18px; font-weight: 800; color: #0d233a; margin: 0 0 20px; }

  .invoice-list { display: flex; flex-direction: column; gap: 12px; }
  .invoice-item { display: flex; flex-direction: column; gap: 16px; padding: 16px 20px; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; }
  @media (min-width: 768px) { .invoice-item { flex-direction: row; align-items: center; justify-content: space-between; } }

  .inv-info { display: flex; flex-direction: column; gap: 4px; }
  .inv-id { font-family: monospace; font-size: 11px; font-weight: 800; color: #15803d; }
  .inv-task { font-size: 15px; font-weight: 800; color: #0f172a; margin: 0; }
  .inv-meta { font-size: 12px; color: #64748b; margin: 0; }
  .inv-meta strong { color: #334155; }

  .inv-action-col { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
  .inv-amount-box { text-align: right; }
  .inv-amount { display: block; font-size: 15px; font-weight: 800; color: #0f172a; }
  .inv-status { font-size: 11px; color: #166534; font-weight: 700; }

  .btn-download { background: #0d233a; color: white; border: none; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; transition: background 0.2s; white-space: nowrap; }
  .btn-download:hover { background: #1e3a8a; }

  /* Dark Mode */
  :global(body.dark-theme .page-header), :global(body.dark-theme .card) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .page-title), :global(body.dark-theme .section-title), :global(body.dark-theme .inv-task), :global(body.dark-theme .inv-amount) { color: #ffffff !important; }
  :global(body.dark-theme .page-sub), :global(body.dark-theme .inv-meta), :global(body.dark-theme .sum-lbl) { color: #94a3b8 !important; }
  :global(body.dark-theme .summary-box-header), :global(body.dark-theme .invoice-item) { background-color: #0f172a !important; border-color: #334155 !important; }
</style>