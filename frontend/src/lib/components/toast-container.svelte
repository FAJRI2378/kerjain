<script>
	import { toasts } from '$lib/ui/toast.svelte.js';
</script>

<div class="fixed top-4 right-4 z-[100] space-y-2 w-80">
	{#each $toasts as t (t.id)}
		<div
			class="px-4 py-3 rounded-xl shadow-lg text-xs font-semibold border backdrop-blur
			{t.type === 'success' ? 'bg-emerald-950/90 border-emerald-700 text-emerald-200'
				: t.type === 'error' ? 'bg-red-950/90 border-red-700 text-red-200'
				: 'bg-slate-900/95 border-slate-700 text-slate-200'}"
		>
			{t.message}
		</div>
	{/each}
</div>
