/**
 * Order status messages for customer-facing receipt
 */

export const STATUS_MESSAGES = {
    pending: {
        label: 'Waiting merchant to confirm',
        note: 'Tunggu konfirmasi penjual sebelum melakukan transfer pembayaran',
    },
    confirmed: {
        label: 'Confirmed',
        note: 'Order terkonfirmasi, silahkan melakukan pembayaran',
    },
    paid: {
        label: 'Telah Lunas',
        note: 'Pembayaran telah diterima penjual',
    },
};

/**
 * Get status label for display
 */
export function getStatusLabel(status) {
    return STATUS_MESSAGES[status]?.label || status;
}

/**
 * Get status note for display
 */
export function getStatusNote(status) {
    return STATUS_MESSAGES[status]?.note || '';
}

/**
 * Get full payment message (note + payment instructions if confirmed)
 */
export function getPaymentMessage(status, paymentNotes = null) {
    const baseNote = getStatusNote(status);

    if (status === 'confirmed' && paymentNotes) {
        return `${baseNote}:\n${paymentNotes}`;
    }

    return baseNote;
}
