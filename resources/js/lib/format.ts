export function formatDate(value?: string | Date | null): string {
    if (value === undefined || value === null || value === '') {
        return '-';
    }

    const raw = String(value);
    const date = new Date(raw);
    if (Number.isNaN(date.getTime())) return raw;

    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = date.toLocaleString('en', {
        month: 'short',
        timeZone: 'UTC',
    });
    const year = String(date.getUTCFullYear()).slice(-2);
    return `${day} ${month} ${year}`;
}

export function formatMonth(value?: string | Date | null): string {
    if (value === undefined || value === null || value === '') {
        return '-';
    }

    const raw = String(value);
    const date = new Date(raw);
    if (Number.isNaN(date.getTime())) return raw;

    return date.toLocaleString('en', {
        month: 'short',
        year: 'numeric',
        timeZone: 'UTC',
    });
}

export function formatCurrency(value?: number | string | null): string {
    if (value === undefined || value === null || value === '') return 'Rp 0';
    const amount = Number(value);

    if (!Number.isFinite(amount)) {
        return 'Rp 0';
    }

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
}
