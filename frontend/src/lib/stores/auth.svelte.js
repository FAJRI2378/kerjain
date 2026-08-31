import { api } from '$lib/api/client.js';

function createAuth() {
	let user = $state(null);
	let token = $state(null);
	let loading = $state(true);
	let hydrated = $state(false);

	function setSession(nextUser, nextToken) {
		user = nextUser;
		token = nextToken;
		hydrated = true;
		if (nextToken) {
			try {
				localStorage.setItem('kerjain_token', nextToken);
			} catch {
				/* ignore */
			}
		} else {
			try {
				localStorage.removeItem('kerjain_token');
			} catch {
				/* ignore */
			}
		}
	}

	async function hydrate() {
		loading = true;
		const stored = (() => {
			try {
				return localStorage.getItem('kerjain_token');
			} catch {
				return null;
			}
		})();

		if (!stored) {
			setSession(null, null);
			loading = false;
			return;
		}

		try {
			const data = await api.get('/api/auth/user');
			setSession(data.data ?? data, stored);
		} catch (err) {
			if (err.status === 401 || err.status === 403) {
				setSession(null, null);
			} else {
				setSession(null, stored);
			}
		} finally {
			loading = false;
		}
	}

	async function login(payload) {
		const data = await api.post('/api/auth/login', payload);
		setSession(data.data.user, data.data.token);
	}

	async function register(payload) {
		const data = await api.post('/api/auth/register', payload);
		setSession(data.data.user, data.data.token);
	}

	async function logout() {
		try {
			await api.post('/api/auth/logout');
		} catch {
			/* ignore server errors on logout */
		}
		setSession(null, null);
	}

	const me = $derived(user);

	return {
		get user() {
			return user;
		},
		get token() {
			return token;
		},
		get loading() {
			return loading;
		},
		get hydrated() {
			return hydrated;
		},
		get me() {
			return me;
		},
		setSession,
		hydrate,
		login,
		register,
		logout
	};
}

export const auth = createAuth();

export function hasRole(...roles) {
	return auth.user && roles.includes(auth.user.role);
}
