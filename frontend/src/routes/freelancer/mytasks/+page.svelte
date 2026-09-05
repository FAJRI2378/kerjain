<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let myTasks = $state([]);
  let loading = $state(true);
  let proofOpen = $state({});
  let proofInput = $state({});
  let submittingId = $state(null);
  
  // State untuk Cetak Invoice
  let invoiceData = $state(null);

  // Definisi Kolom Kanban menyesuaikan status API
  const columns = [
    { id: 'in_progress', title: '📋 Dalam Pengerjaan', color: 'border-slate-200' },
    { id: 'reviewing', title: '⏳ Sedang Direview', color: 'border-blue-200' },
    { id: 'completed', title: '✅ Selesai (Siap Invoice)', color: 'border-green-200' }
  ];

  async function load() {
    loading = true;
    try {
      const res = await api.get('/api/tasks/mine');
      myTasks = res.data ?? [];
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat tugas.'), 'error');
    } finally {
      loading = false;
    }
  }

  onMount(load);

  async function submitProof(taskId) {
    const url = (proofInput[taskId] ?? '').trim();
    if (!url) {
      toast('Masukkan link bukti pekerjaan terlebih dahulu.', 'error');
      return;
    }
    submittingId = taskId;
    try {
      await api.post(`/api/tasks/${taskId}/submit`, { proof_url: url });
      
      // Update state lokal: Otomatis memindahkan kartu ke kolom 'reviewing'
      myTasks = myTasks.map((t) => (t.id === taskId ? { ...t, status: 'reviewing', proof_url: url } : t));
      
      proofOpen[taskId] = false;
      toast('Bukti kerja terkirim! Tugas pindah ke tahap Review.', 'success');
    } catch (err) {
      toast(errorMessage(err, 'Gagal mengirim bukti.'), 'error');
    } finally {
      submittingId = null;
    }
  }

  function printInvoice(task) {
    invoiceData = task;
    // Beri sedikit jeda agar DOM ter-render sebelum memanggil dialog print browser
    setTimeout(() => {
      window.print();
    }, 150);
  }
</script>

<div class="tasks-page font-sans">
  <!-- Header (Disembunyikan saat print) -->
  <div class="page-header no-print">
    <h1 class="page-title">Tugas Saya </h1>
    <p class="page-sub">Pantau alur kerja Anda. Kartu akan otomatis berpindah kolom setelah bukti dikirim.</p>
  </div>

  {#if loading}
    <div class="loading-state no-print">
      <div class="spinner"></div>
      <p>Memuat tugas...</p>
    </div>
  {:else if myTasks.length === 0}
    <div class="empty-state no-print">
      <p>Belum ada tugas yang kamu kerjakan. Cek daftar lowongan untuk mulai bekerja!</p>
    </div>
  {:else}
    <!-- Kanban Board Area (Disembunyikan saat print) -->
    <div class="kanban-board no-print">
      {#each columns as col}
        <div class="kanban-column {col.color}">
          <h3 class="column-title">{col.title}</h3>
          
          <div class="kanban-cards">
            <!-- Filter task berdasarkan status API. Asumsi status selesai = 'completed' atau 'done' -->
            {#each myTasks.filter(t => t.status === col.id || (col.id === 'completed' && t.status === 'done')) as task (task.id)}
              <div class="task-card">
                <div class="task-head font-mono">
                  <span class="task-id">#{task.id}</span>
                </div>

                <h4 class="task-title">{task.title}</h4>
                <p class="task-owner">🏢 {task.owner?.name ?? '-'}</p>
                
                {#if task.proof_url}
                  <a href={task.proof_url} target="_blank" rel="noreferrer" class="proof-link">
                    📎 Lihat Bukti Terkirim
                  </a>
                {/if}

                <div class="task-footer">
                  <span class="task-price">{formatRupiah(task.budget || task.price || 0)}</span>
                  
                  <!-- Tombol Aksi Berdasarkan Kolom -->
                  {#if task.status === 'in_progress'}
                    <div class="proof-action-group">
                      {#if proofOpen[task.id]}
                        <div class="proof-input-box">
                          <input
                            bind:value={proofInput[task.id]}
                            placeholder="Link (Drive/Imgur)"
                          />
                          <button 
                            onclick={() => submitProof(task.id)} 
                            disabled={submittingId === task.id}
                            class="btn-send">
                            {submittingId === task.id ? '...' : 'Kirim'}
                          </button>
                        </div>
                      {:else}
                        <button onclick={() => proofOpen[task.id] = true} class="btn-upload">
                          Upload Bukti
                        </button>
                      {/if}
                    </div>
                  {:else if task.status === 'reviewing'}
                    <span class="badge-waiting">⏳ Menunggu</span>
                  {:else}
                    <button class="btn-print" onclick={() => printInvoice(task)}>
                      🖨️ Cetak Invoice
                    </button>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        </div>
      {/each}
    </div>
  {/if}

  <!-- Template PDF Invoice (Hanya muncul saat CTRL+P / Diprint) -->
  {#if invoiceData}
    <div class="invoice-print-container print-only">
      <div class="invoice-header">
        <h1>INVOICE PEMBAYARAN</h1>
        <p><strong>Platform:</strong> Kerjain Freelancer</p>
        <p><strong>ID Tugas:</strong> #{invoiceData.id}</p>
        <p><strong>Tanggal Cetak:</strong> {new Date().toLocaleDateString('id-ID')}</p>
      </div>
      
      <div class="invoice-body">
        <p><strong>Ditagihkan kepada UMKM:</strong> {invoiceData.owner?.name ?? '-'}</p>
        
        <table class="invoice-table">
          <thead>
            <tr>
              <th>Deskripsi Pekerjaan</th>
              <th>Status</th>
              <th>Total Biaya</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{invoiceData.title}</td>
              <td>Selesai</td>
              <td class="font-bold">{formatRupiah(invoiceData.budget || invoiceData.price || 0)}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="invoice-footer">
        <p class="lunas-stamp">LUNAS</p>
        <p>Terima kasih atas kepercayaannya menggunakan jasa Freelancer kami.</p>
      </div>
    </div>
  {/if}
</div>

<style>
  :global(body) {
    margin: 0;
    padding: 0;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: #f8fafc;
    color: #0f172a;
    -webkit-font-smoothing: antialiased;
  }

  * { box-sizing: border-box; }

  .tasks-page {
    padding: 32px 24px;
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
  }

  .page-sub {
    color: #64748b;
    font-size: 14px;
    margin: 0;
  }

  .loading-state, .empty-state {
    text-align: center;
    padding: 48px;
    color: #64748b;
    background: #ffffff;
    border-radius: 16px;
    border: 1px dashed #cbd5e1;
  }

  /* KANBAN BOARD STYLES */
  .kanban-board {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding-bottom: 16px;
    min-height: 60vh;
  }

  .kanban-column {
    flex: 1;
    min-width: 320px;
    background: #f8fafc;
    border-top: 4px solid;
    border-radius: 12px;
    padding: 16px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
  }

  .column-title {
    font-size: 15px;
    font-weight: 800;
    color: #334155;
    margin: 0 0 16px;
  }

  .kanban-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .task-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 18px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .task-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transform: translateY(-2px);
  }

  .task-head {
    margin-bottom: 8px;
  }
  .task-id {
    font-size: 12px;
    font-weight: 700;
    color: #15803d;
    background: #dcfce7;
    padding: 2px 6px;
    border-radius: 4px;
  }

  .task-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px;
    line-height: 1.4;
  }

  .task-owner {
    font-size: 12px;
    color: #64748b;
    margin: 0 0 8px;
  }

  .proof-link {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    padding: 4px 8px;
    border-radius: 6px;
    text-decoration: none;
    margin-bottom: 12px;
  }

  .task-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #e2e8f0;
    padding-top: 14px;
    margin-top: 6px;
  }

  .task-price {
    font-size: 14px;
    font-weight: 800;
    color: #15803d;
  }

  /* Inputs & Buttons */
  .proof-input-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
  }

  .proof-input-box input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 11px;
    outline: none;
  }
  
  .proof-input-box input:focus { border-color: #15803d; }

  .btn-upload, .btn-send, .btn-print {
    background-color: #15803d;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
  }
  
  .btn-upload:hover, .btn-send:hover { background-color: #166534; }

  .btn-print {
    background-color: #0d233a;
  }
  .btn-print:hover { background-color: #1e3a8a; }

  .badge-waiting {
    font-size: 11px;
    font-weight: 600;
    color: #d97706;
    background: #fef3c7;
    padding: 4px 8px;
    border-radius: 6px;
  }

  /* ======= CSS KHUSUS CETAK PDF / INVOICE ======= */
  .print-only { display: none; }

  @media print {
    :global(body) { background: white !important; }
    
    /* Sembunyikan elemen UI Aplikasi */
    .no-print, :global(.sidebar), :global(.mobile-header) {
      display: none !important;
    }
    
    /* Tampilkan Format Invoice */
    .print-only {
      display: block !important;
      width: 100%;
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      color: black;
      font-family: Arial, sans-serif;
    }

    .invoice-header {
      border-bottom: 2px solid #000;
      padding-bottom: 16px;
      margin-bottom: 24px;
    }
    
    .invoice-header h1 {
      font-size: 28px;
      margin: 0 0 8px 0;
      color: #0d233a;
    }

    .invoice-header p { margin: 4px 0; font-size: 14px; }
    
    .invoice-body p { font-size: 14px; margin-bottom: 12px; }

    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    
    .invoice-table th, .invoice-table td {
      border: 1px solid #cbd5e1;
      padding: 12px;
      text-align: left;
      font-size: 14px;
    }
    
    .invoice-table th {
      background-color: #f1f5f9;
      font-weight: bold;
    }
    
    .invoice-footer {
      margin-top: 60px;
      text-align: right;
    }

    .lunas-stamp {
      display: inline-block;
      font-size: 24px;
      font-weight: 900;
      color: #15803d;
      border: 4px solid #15803d;
      padding: 8px 24px;
      transform: rotate(-10deg);
      opacity: 0.7;
      margin-bottom: 20px;
    }
  }
</style>