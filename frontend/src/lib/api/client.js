const BASE = import.meta.env.PUBLIC_API_BASE ?? '';

export class ApiError extends Error {
	constructor(message, status, data) {
		super(message);
		this.name = 'ApiError';
		this.status = status;
		this.data = data;
	}
}

function getToken() {
	try {
		return localStorage.getItem('kerjain_token');
	} catch {
		return null;
	}
}

export async function request(path, { method = 'GET', body, headers = {}, auth = true } = {}) {
	const init = { method, headers: { ...headers } };

	if (auth) {
		const token = getToken();
		if (token) init.headers['Authorization'] = `Bearer ${token}`;
	}

	if (body !== undefined) {
		init.headers['Content-Type'] = 'application/json';
		init.body = JSON.stringify(body);
	}

	const response = await fetch(`${BASE}${path}`, init);

	let data = null;
	if (response.status !== 204) {
		try {
			data = await response.json();
		} catch {
			data = null;
		}
	}

	if (!response.ok) {
		const message = data?.message ?? `Request failed (${response.status})`;
		throw new ApiError(message, response.status, data);
	}

	return data;
}

export const api = {
	get: (path, options) => request(path, { ...options, method: 'GET' }),
	post: (path, body, options) => request(path, { ...options, method: 'POST', body }),
	put: (path, body, options) => request(path, { ...options, method: 'PUT', body }),
	delete: (path, options) => request(path, { ...options, method: 'DELETE' })
};
