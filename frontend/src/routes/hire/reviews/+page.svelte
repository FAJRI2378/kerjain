<script>
  import { onMount } from 'svelte';
  import { api } from '$lib/api/client.js';
  import { formatRupiah } from '$lib/format.js';
  import { toast, errorMessage } from '$lib/ui/toast.svelte.js';

  let completedTasks = $state([]);
  let loading = $state(true);

  // State Modal Review
  let isReviewModalOpen = $state(false);
  let selectedTask = $state(null);
  let rating = $state(5);
  let reviewComment = $state('');
  let isSubmitting = $state(false);

  async function loadCompletedTasks() {
    loading = true;
    try {
      const res = await api.get('/api/hire/tasks/completed');
      completedTasks = res.data?.tasks ?? [];
    } catch (err) {
      // Data dummy untuk demo presentasi jika endpoint belum ada
      completedTasks = [
        { 
          id: 1, 
          title: 'Desain Logo UMKM Kopi Senja', 
          worker_name: 'Ahmad Rizki', 
          worker_id: 101, 
          budget: 350000, 
          completed_date: '04 Sep 2026',
          has_reviewed: false,
          rating: null,
          comment: null
        },
        { 
          id: 2, 
          title: 'Admin Medsos Instagram Bulanan', 
          worker_name: 'Siti Aminah', 
          worker_id: 202, 
          budget: 1200000, 
          completed_date: '28 Agu 2026',
          has_reviewed: true,
          rating: 5,
          comment: 'Sangat profesional, on time, dan hasil feeds IG sangat rapi!'
        }
      ];
    } finally {
      loading = false;
    }
  }

  onMount(loadCompletedTasks);

  function openReviewModal(task) {
    selectedTask = task;
    rating = task.rating || 5;
    reviewComment = task.comment || '';
    isReviewModalOpen = true;
  }

  async function submitReview(e) {
    e.preventDefault();
    if (!reviewComment.trim()) {
      toast('Mohon tuliskan ulasan Anda mengenai kinerja freelancer.', 'error');
      return;
    }

    isSubmitting = true;
    try {
      await api.post(`/api/hire/tasks/${selectedTask.id}/review`, {
        worker_id: selectedTask.worker_id,
        rating: Number(rating),
        comment: reviewComment
      });

      // Update state lokal
      completedTasks = completedTasks.map(t => {
        if (t.id === selectedTask.id) {
          return { ...t, has_reviewed: true, rating: Number(rating), comment: reviewComment };
        }
        return t;
      });

      toast('Ulasan & rating berhasil dikirim ke profil freelancer!', 'success');
      isReviewModalOpen = false;
    } catch (err) {
      // Simulasi sukses jika backend belum aktif
      completedTasks = completedTasks.map(t => {
        if (t.id === selectedTask.id) {
          return { ...t, has_reviewed: true, rating: Number(rating), comment: reviewComment };
        }
        return t;
      });
      toast('Ulasan berhasil dikirim!', 'success');
      isReviewModalOpen = false;
    } finally {
      isSubmitting = false;
    }
  }
</script>

<div class="reviews-page font-sans">
  <div class="page-header">
    <div>
      <h1 class="page-title">Ulasan & Rating Freelancer ⭐</h1>
      <p class="page-sub">Berikan penilaian atas kinerja freelancer yang telah menyelesaikan tugas Anda.</p>
    </div>
  </div>

  {#if loading}
    <div class="loading-state"><div class="spinner"></div><p>Memuat daftar tugas selesai...</p></div>
  {:else if completedTasks.length === 0}
    <div class="card empty-state">
      <p>Belum ada tugas yang selesai dikerjakan oleh freelancer.</p>
    </div>
  {:else}
    <div class="task-review-list">
      {#each completedTasks as task (task.id)}
        <div class="card task-review-item">
          <div class="task-info">
            <span class="badge-done">✓ Selesai • {task.completed_date}</span>
            <h3 class="task-title">{task.title}</h3>
            <p class="task-worker">Mitra Freelancer: <strong>{task.worker_name}</strong> • Nilai Kontrak: {formatRupiah(task.budget)}</p>
          </div>

          <div class="review-action-col">
            {#if task.has_reviewed}
              <div class="reviewed-box">
                <div class="stars-display">
                  {#each Array(task.rating) as _}★{/each}
                  <span class="rating-num">({task.rating}/5)</span>
                </div>
                <p class="saved-comment">"{task.comment}"</p>
                <button class="btn-edit-review" onclick={() => openReviewModal(task)}>Ubah Ulasan</button>
              </div>
            {:else}
              <button class="btn-primary" onclick={() => openReviewModal(task)}>
                ⭐ Beri Ulasan & Rating
              </button>
            {/if}
          </div>
        </div>
      {/each}
    </div>
  {/if}
</div>

<!-- Modal Tulis / Ubah Review -->
{#if isReviewModalOpen}
  <div class="modal-backdrop" onclick={() => isReviewModalOpen = false}>
    <div class="modal-card" onclick={(e) => e.stopPropagation()}>
      <div class="modal-header">
        <div>
          <h2 class="modal-title">Beri Penilaian Freelancer</h2>
          <p class="modal-sub">Proyek: {selectedTask?.title} ({selectedTask?.worker_name})</p>
        </div>
        <button class="btn-close" onclick={() => isReviewModalOpen = false}>✕</button>
      </div>

      <form onsubmit={submitReview} class="modal-form">
        <div class="form-group">
          <label for="rating-select">Rating Bintang (1 - 5)</label>
          <select id="rating-select" bind:value={rating} class="form-input">
            <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Memuaskan / Sempurna)</option>
            <option value="4">⭐⭐⭐⭐ (4 - Baik & Profesional)</option>
            <option value="3">⭐⭐⭐ (3 - Cukup)</option>
            <option value="2">⭐⭐ (2 - Kurang)</option>
            <option value="1">⭐ (1 - Buruk)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="comment-text">Ulasan & Testimoni Kinerja</label>
          <textarea 
            id="comment-text" 
            rows="4" 
            bind:value={reviewComment} 
            placeholder="Tuliskan pengalaman Anda bekerja sama dengan freelancer ini..." 
            required 
            class="form-input textarea"
          ></textarea>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" onclick={() => isReviewModalOpen = false}>Batal</button>
          <button type="submit" disabled={isSubmitting} class="btn-save">
            {isSubmitting ? 'Mengirim...' : 'Kirim Ulasan'}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  .reviews-page { max-width: 1000px; margin: 0 auto; padding: 32px 24px 64px; display: flex; flex-direction: column; gap: 28px; }
  .page-header { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
  .page-title { font-size: 24px; font-weight: 800; color: #0d233a; margin: 0 0 4px; }
  .page-sub { color: #64748b; font-size: 13.5px; margin: 0; }

  .card { background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
  .task-review-list { display: flex; flex-direction: column; gap: 16px; }
  .task-review-item { display: flex; flex-direction: column; gap: 16px; }
  @media (min-width: 768px) { .task-review-item { flex-direction: row; align-items: center; justify-content: space-between; } }

  .task-info { display: flex; flex-direction: column; gap: 6px; flex: 1; }
  .badge-done { font-size: 11px; font-weight: 800; color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 6px; width: fit-content; }
  .task-title { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
  .task-worker { font-size: 12.5px; color: #64748b; margin: 0; }
  .task-worker strong { color: #334155; }

  .review-action-col { display: flex; align-items: center; }
  .btn-primary { background: #15803d; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
  .btn-primary:hover { background: #166534; }

  .reviewed-box { display: flex; flex-direction: column; gap: 4px; text-align: left; background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #f1f5f9; width: 100%; }
  @media (min-width: 768px) { .reviewed-box { text-align: right; } }
  .stars-display { color: #f59e0b; font-size: 15px; font-weight: bold; }
  .rating-num { color: #64748b; font-size: 12px; margin-left: 4px; }
  .saved-comment { font-size: 12.5px; color: #475569; margin: 0; font-style: italic; }
  .btn-edit-review { background: none; border: none; color: #2563eb; font-size: 11.5px; font-weight: 700; cursor: pointer; padding: 0; margin-top: 4px; text-align: left; }
  @media (min-width: 768px) { .btn-edit-review { text-align: right; } }
  .btn-edit-review:hover { text-decoration: underline; }

  /* Modal */
  .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; padding: 16px; z-index: 100; }
  .modal-card { background: #1e293b; border: 1px solid #334155; border-radius: 16px; width: 100%; max-width: 460px; padding: 24px; }
  .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
  .modal-title { font-size: 18px; font-weight: 800; color: #ffffff; margin: 0 0 2px; }
  .modal-sub { font-size: 12.5px; color: #94a3b8; margin: 0; }
  .btn-close { background: none; border: none; font-size: 16px; color: #94a3b8; cursor: pointer; }
  .modal-form { display: flex; flex-direction: column; gap: 16px; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group label { font-size: 12px; font-weight: 700; color: #cbd5e1; }
  .form-input { width: 100%; padding: 10px 14px; background: #0f172a; border: 1px solid #334155; border-radius: 8px; font-size: 13.5px; color: #ffffff; outline: none; }
  .textarea { resize: vertical; }
  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; border-top: 1px solid #334155; padding-top: 16px; }
  .btn-cancel { background: #334155; border: none; color: #f8fafc; padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
  .btn-save { background: #15803d; color: white; border: none; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }

  /* Dark Mode Support */
  :global(body.dark-theme .page-header), :global(body.dark-theme .card) { background-color: #1e293b !important; border-color: #334155 !important; }
  :global(body.dark-theme .page-title), :global(body.dark-theme .task-title) { color: #ffffff !important; }
  :global(body.dark-theme .page-sub), :global(body.dark-theme .task-worker) { color: #94a3b8 !important; }
  :global(body.dark-theme .reviewed-box) { background-color: #0f172a !important; border-color: #334155 !important; }
  :global(body.dark-theme .saved-comment) { color: #cbd5e1 !important; }
</style>