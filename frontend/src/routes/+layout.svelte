<script>
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import '../app.css';
  import ToastContainer from '$lib/components/toast-container.svelte';
  import MaintenanceScreen from '$lib/components/maintenance-screen.svelte';
  import { auth } from '$lib/stores/auth.svelte.js';
  import { maintenance } from '$lib/stores/maintenance.svelte.js';
  import { theme } from '$lib/stores/theme.js'; // <- 1. Import store theme di sini

  let { children } = $props();

  const isAdminRoute = $derived($page.url.pathname.startsWith('/admin'));

  onMount(() => {
    if (!auth.hydrated) {
      auth.hydrate();
    }
    if (!maintenance.checked) {
      maintenance.hydrate();
    }
    // 2. Panggil theme.init() di dalam onMount layout utama
    theme.init();
  });
</script>

<div class="min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">
  {#if maintenance.active && !isAdminRoute && $page.url.pathname !== '/login'}
    <MaintenanceScreen />
  {:else}
    <ToastContainer />
    {@render children()}
  {/if}
</div>