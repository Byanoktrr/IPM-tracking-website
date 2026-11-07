import Alpine from 'alpinejs';

// Setup CSRF header helper
window.getCsrfToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

// Rupiah formatting helper
window.formatRupiah = (value) => {
    if (value === null || value === undefined || value === '') return 'Rp 0';
    const num = Number(value) || 0;
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
};

window.parseRupiah = (value) => {
    if (!value) return 0;
    if (typeof value === 'number') return value;
    const clean = String(value).replace(/[^0-9]/g, '');
    return Number(clean) || 0;
};

// Notification helper
window.showToast = (message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('toast-notify', {
        detail: { message, type }
    }));
};

window.Alpine = Alpine;
Alpine.start();
