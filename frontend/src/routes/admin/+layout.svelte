<script>
  import { onMount } from 'svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  let { children } = $props();

  onMount(() => {
    if (!auth.hydrated) auth.hydrate();
  });
</script>

<div class="min-h-screen bg-slate-950 text-slate-100 selection:bg-purple-500 selection:text-white">
  {@render children()}
</div>
