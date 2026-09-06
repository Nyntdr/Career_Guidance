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