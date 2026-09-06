import { api } from '$lib/api/client.js';

let maintenanceMode = $state(false);
let message = $state('');
let checked = $state(false);

async function hydrate() {
	try {
		const res = await api.get('/api/status', { auth: false });
		maintenanceMode = !!res?.data?.maintenance_mode;
		message = res?.data?.message ?? 'Kami sedang melakukan pemeliharaan sistem. Mohon kembali beberapa saat lagi.';
	} catch {
		maintenanceMode = false;
	} finally {
		checked = true;
	}
}

export const maintenance = {
	get active() {
		return maintenanceMode;
	},
	get message() {
		return message;
	},
	get checked() {
		return checked;
	},
	set(state) {
		maintenanceMode = state;
	},
	hydrate
};