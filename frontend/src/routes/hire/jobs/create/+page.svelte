<script>
  import { goto } from '$app/navigation';
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let title = $state('');
  let category = $state('');
  let location = $state('');
  let budget = $state(150000);
  let description = $state('');
  let categories = $state([]);
  let submitting = $state(false);
  let formErrors = $state({});

  onMount(async () => {
    try {
      const res = await api.get('/api/categories');
      categories = res.data ?? [];
      if (categories.length && !category) category = categories[0].slug;
    } catch (err) {
      toast(errorMessage(err, 'Gagal memuat kategori.'), 'error');
    }
  });

  async function handleSubmit(e) {
    e.preventDefault();
    submitting = true;
    formErrors = {};
    try {
      await api.post('/api/tasks', {
        title,
        category,
        location,
        budget: Number(budget),
        description
      });
      toast(`Tugas "${title}" berhasil ditayangkan dan budget dipindahkan sementara ke Escrow!`, 'success');
      goto('/hire/jobs');
    } catch (err) {
      if (err.status === 422 && err.data?.errors) {
        formErrors = err.data.errors;
      } else {
        toast(errorMessage(err, 'Gagal membuat tugas.'), 'error');
      }
    } finally {
      submitting = false;
    }
  }
</script>

<div class="create-job-page font-sans">
  <!-- Header -->
  <div class="page-header">
    <h1 class="page-title">Buat Tugas / Pekerjaan Baru</h1>
    <p class="page-sub">Post tugas harian atau sampingan untuk diselesaikan oleh freelancer terverifikasi.</p>
  </div>

  <form onsubmit={handleSubmit} class="card form-card">
    <!-- Title -->
    <div class="form-group">
      <label for="title">Judul Tugas / Pekerjaan</label>
      <input 
        id="title"
        type="text" 
        placeholder="Contoh: Jasa Sebar Brosur Toko 500 Lembar" 
        bind:value={title}
        required
        class="form-input"
      />
      {#if formErrors.title}<p class="error-text">{(formErrors.title).join(', ')}</p>{/if}
    </div>

    <!-- Category & Location -->
    <div class="form-row">
      <div class="form-group">
        <label for="category">Kategori</label>
        <select 
          id="category"
          bind:value={category}
          class="form-input"
        >
          {#each categories as cat (cat.id)}
            <option value={cat.slug}>{cat.name}</option>
          {/each}
        </select>
        {#if formErrors.category}<p class="error-text">{(formErrors.category).join(', ')}</p>{/if}
      </div>

      <div class="form-group">
        <label for="location">Lokasi Kerja</label>
        <input 
          id="location"
          type="text" 
          placeholder="Contoh: Remote atau Jakarta Selatan" 
          bind:value={location}
          required
          class="form-input"
        />
        {#if formErrors.location}<p class="error-text">{(formErrors.location).join(', ')}</p>{/if}
      </div>
    </div>

    <!-- Budget -->
    <div class="form-group">
      <label for="budget">Budget Honor Freelancer (Rp)</label>
      <input 
        id="budget"
        type="number" 
        min="50000"
        step="10000"
        bind:value={budget}
        required
        class="form-input"
      />
      {#if formErrors.budget}<p class="error-text">{(formErrors.budget).join(', ')}</p>{/if}
      <p class="form-tip">Dana akan dikunci di Escrow dan hanya dikirim ke freelancer saat pekerjaan disetujui.</p>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label for="desc">Detail Instruksi Tugas</label>
      <textarea 
        id="desc"
        rows="4"
        placeholder="Jelaskan instruksi pengerjaan, lokasi pengambilan berkas, atau kriteria khusus..." 
        bind:value={description}
        required
        class="form-input textarea"
      ></textarea>
      {#if formErrors.description}<p class="error-text">{(formErrors.description).join(', ')}</p>{/if}
    </div>

    <!-- Submit -->
    <div class="form-actions">
      <button 
        type="submit" 
        disabled={submitting}
        class="btn-submit"
      >
        {submitting ? 'Menayangkan...' : 'Tayangkan Tugas & Kunci Dana Escrow'}
      </button>
    </div>
  </form>
</div>

<style>
  .create-job-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 32px 24px 64px;
    display: flex;
    flex-direction: column;
    gap: 28px;
  }

  .page-header {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .page-title {
    font-size: 24px;
    font-weight: 800;
    color: #0d233a;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
  }

  .page-sub {
    font-size: 13.5px;
    color: #64748b;
    margin: 0;
  }

  .card {
    background: #ffffff;
    border-radius: 16px;
    padding: 28px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    transition: background-color 0.3s ease, border-color 0.3s ease;
  }

  .form-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .form-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
  }

  @media (min-width: 640px) {
    .form-row {
      grid-template-columns: 1fr 1fr;
    }
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .form-group label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
  }

  .form-input {
    width: 100%;
    padding: 11px 14px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #0f172a;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background-color 0.3s ease;
  }

  .form-input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
  }

  .textarea {
    resize: vertical;
  }

  .form-tip {
    font-size: 11px;
    color: #64748b;
    margin: 2px 0 0;
  }

  .error-text {
    font-size: 11px;
    color: #ef4444;
    margin: 2px 0 0;
  }

  .form-actions {
    margin-top: 8px;
  }

  .btn-submit {
    width: 100%;
    background: #15803d;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.15);
    transition: background 0.2s, opacity 0.2s;
  }

  .btn-submit:hover:not(:disabled) {
    background: #166534;
  }

  .btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  /* ---------------- Dark Mode Support ---------------- */
  :global(body.dark-theme .page-header),
  :global(body.dark-theme .card) {
    background-color: #1e293b !important;
    border-color: #334155 !important;
  }
  :global(body.dark-theme .page-title) {
    color: #ffffff !important;
  }
  :global(body.dark-theme .page-sub),
  :global(body.dark-theme .form-tip) {
    color: #94a3b8 !important;
  }
  :global(body.dark-theme .form-group label) {
    color: #cbd5e1 !important;
  }
  :global(body.dark-theme .form-input) {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #ffffff !important;
  }
  :global(body.dark-theme .form-input:focus) {
    border-color: #22c55e !important;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15) !important;
  }
</style>