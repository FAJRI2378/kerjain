export function normalizeWaNumber(phone = '') {
	const digits = String(phone).replace(/\D/g, '');
	if (digits.startsWith('0')) return '62' + digits.slice(1);
	return digits;
}

export function waLink(phone = '', text = '') {
	const num = normalizeWaNumber(phone);
	if (!num) return null;
	const url = `https://wa.me/${num}`;
	return text ? `${url}?text=${encodeURIComponent(text)}` : url;
}

export function waPhoneDisplay(phone = '') {
	const digits = String(phone).replace(/\D/g, '');
	if (digits.length === 12 && digits.startsWith('62')) {
		return '0' + digits.slice(2);
	}
	return String(phone);
}