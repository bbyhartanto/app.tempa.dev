/**
 * Status badge utility — scalable approach
 * Adding a new status means one line in this file
 */

export const STATUS_CLASSES = {
    pending:    'bg-yellow-100 text-yellow-800',
    confirmed:  'bg-blue-100 text-blue-800',
    paid:       'bg-green-100 text-green-800',
};

export const STATUS_LABELS = {
    pending:    'Waiting merchant to confirm',
    confirmed:  'Confirmed',
    paid:       'Telah Lunas',
};

/**
 * Get CSS classes for a status
 * @param {string} status - Order status
 * @returns {string} Tailwind classes
 */
export const statusClass = (status) =>
    STATUS_CLASSES[status] ?? 'bg-gray-100 text-gray-800';

/**
 * Get human-readable label for a status
 * @param {string} status - Order status
 * @returns {string} Display label
 */
export const statusLabel = (status) =>
    STATUS_LABELS[status] ?? status.charAt(0).toUpperCase() + status.slice(1);

/**
 * Get payment status classes
 */
export const paymentStatusClass = (status) =>
    status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';

export const paymentStatusLabel = (status) =>
    status === 'paid' ? '✓ Paid' : '⏳ Unpaid';
