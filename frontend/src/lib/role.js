export function roleHome(role) {
	switch (role) {
		case 'freelancer':
			return '/freelancer/dashboard';
		case 'hirer':
			return '/hire/dashboard';
		case 'admin':
			return '/admin/dashboard';
		default:
			return '/';
	}
}
