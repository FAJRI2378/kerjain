<script>
  import { onMount } from 'svelte';
  import '../app.css';
  import ToastContainer from '$lib/components/toast-container.svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  let { children } = $props();

  onMount(() => {
    if (!auth.hydrated) {
      auth.hydrate();
    }
  });
</script>

<div class="min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">
  <ToastContainer />
  {@render children()}
</div>
