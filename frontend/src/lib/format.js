export function formatRupiah(value) {
	const n = Number(value ?? 0);
	return `Rp ${n.toLocaleString('id-ID')}`;
}

export function formatNumber(value) {
	return Number(value ?? 0).toLocaleString('id-ID');
}

export const FREELANCER_STATUS = {
	pending: { label: 'Menunggu', tone: 'text-amber-400' },
	approved: { label: 'Terbuka', tone: 'text-emerald-400' },
	in_progress: { label: 'In Progress', tone: 'text-blue-400' },
	reviewing: { label: 'Sedang Direview', tone: 'text-yellow-400' },
	completed: { label: 'Selesai', tone: 'text-emerald-400' },
	cancelled: { label: 'Dibatalkan', tone: 'text-slate-400' }
};

export const HIRE_STATUS = {
	pending: { label: 'Menunggu Moderasi', tone: 'text-amber-400' },
	approved: { label: 'Terbuka', tone: 'text-emerald-400' },
	rejected: { label: 'Ditolak', tone: 'text-red-400' },
	in_progress: { label: 'In Progress', tone: 'text-blue-400' },
	reviewing: { label: 'Reviewing Proof', tone: 'text-yellow-400' },
	completed: { label: 'Selesai', tone: 'text-emerald-400' },
	cancelled: { label: 'Dibatalkan', tone: 'text-slate-400' }
};

export const ADMIN_STATUS = {
	pending: { label: 'Pending', tone: 'text-amber-400' },
	approved: { label: 'Disetujui', tone: 'text-emerald-400' },
	rejected: { label: 'Ditolak', tone: 'text-red-400' }
};

export function initials(name = '') {
	const parts = String(name).trim().split(/\s+/);
	const first = parts[0]?.[0] ?? '';
	const last = parts.length > 1 ? parts[parts.length - 1][0] : '';
	return (first + last).toUpperCase();
}
