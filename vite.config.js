import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite'; // <-- Import paket Tailwind v4

export default defineConfig({
	plugins: [
		tailwindcss(), // <-- Dipanggil dengan benar di sini
		sveltekit({
			compilerOptions: {
				runes: ({ filename }) =>
					filename.split(/[/\\]/).includes('node_modules') ? undefined : true
			}
		})
	]
});