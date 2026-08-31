import { get } from 'svelte/store';
import { writable } from 'svelte/store';

export const toasts = writable([]);

let id = 0;

export function toast(message, type = 'info') {
	const toastId = ++id;
	toasts.update((list) => [...list, { id: toastId, message, type }]);
	setTimeout(() => {
		toasts.update((list) => list.filter((t) => t.id !== toastId));
	}, 4000);
}

export function fieldErrors(errors = {}) {
	return Object.values(errors).flat().join(' ');
}

export function errorMessage(error, fallback = 'Terjadi kesalahan.') {
	if (error?.status === 422) {
		return fieldErrors(error.data?.errors) || error.message;
	}
	return error?.message || fallback;
}

export function readToasts() {
	return get(toasts);
}
