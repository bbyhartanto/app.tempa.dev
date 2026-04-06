/**
 * Currency formatting utilities
 */

const CURRENCY_CONFIG = {
    IDR: { symbol: 'Rp', locale: 'id-ID', decimals: 0 },
    USD: { symbol: '$', locale: 'en-US', decimals: 2 },
    EUR: { symbol: '€', locale: 'de-DE', decimals: 2 },
};

/**
 * Format currency amount
 * @param {number} amount - The amount to format
 * @param {string} currency - Currency code (default: 'IDR')
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, currency = 'IDR') {
    const config = CURRENCY_CONFIG[currency.toUpperCase()] || CURRENCY_CONFIG.IDR;

    if (config.decimals === 0) {
        return `${config.symbol} ${Number(amount).toLocaleString(config.locale)}`;
    }

    return `${config.symbol} ${Number(amount).toLocaleString(config.locale, {
        minimumFractionDigits: config.decimals,
        maximumFractionDigits: config.decimals,
    })}`;
}

/**
 * Parse currency string to number (for input fields)
 * @param {string} value - Currency string to parse
 * @returns {number} Parsed number
 */
export function parseCurrency(value) {
    if (!value) return 0;
    return parseFloat(String(value).replace(/[^\d.-]/g, '')) || 0;
}
