export function formatMoney(n, symbol = '$', position = 'before') {
    const value = Number(n).toLocaleString();
    return position === 'after' ? `${value}${symbol}` : `${symbol}${value}`;
}

export function brandInitials(name, fallback = 'UB') {
    const words = String(name || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (words.length === 0) return fallback;
    if (words.length === 1) return words[0].slice(0, 2).toUpperCase();

    return (words[0][0] + words[1][0]).toUpperCase();
}

export function scoreColor(score) {
    if (score >= 70) return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300';
    if (score >= 40) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300';
    return 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300';
}