import { ref, computed } from 'vue';

/**
 * useBooking Composable
 *
 * Handles booking flow: date/time selection, customer info, and order creation.
 */
export function useBooking(tenant, service, onBookingSuccess = null) {
    const showBookingForm = ref(false);
    const showSuccessModal = ref(false);
    const lastOrderNumber = ref(null);
    const lastReceiptUrl = ref(null);
    const processing = ref(false);
    const errors = ref({});

    const form = ref({
        customer_name: '',
        customer_phone: '',
        customer_instagram: '',
        booking_date: '',
        booking_time_slot: '',
    });

    /**
     * Generate time slots for a given date based on service config.
     * NOTE: Now deprecated - time is selected directly via datetime-local input
     */
    function generateTimeSlots(date) {
        // No longer needed with datetime-local picker
        return [];
    }

    /**
     * Get minimum selectable date (today).
     */
    function getMinDate() {
        const now = new Date();
        const year = now.getFullYear();
        const month = (now.getMonth() + 1).toString().padStart(2, '0');
        const day = now.getDate().toString().padStart(2, '0');
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    /**
     * Generate Google Calendar link.
     */
    function generateGoogleCalendarLink(bookingDate, timeSlot) {
        if (!bookingDate || !timeSlot) return null;

        // Parse time slot (e.g., "14:30-16:00")
        const times = timeSlot.split('-');
        if (times.length !== 2) return null;

        const date = bookingDate.replace(/-/g, '');
        const startTime = times[0].replace(':', '') + '00';
        const endTime = times[1].replace(':', '') + '00';

        const title = encodeURIComponent('Booking at ' + tenant.value?.name);
        const location = tenant.value?.address ? encodeURIComponent(tenant.value.address) : '';
        const details = encodeURIComponent('Booking confirmed via WhatsApp.');

        return `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${date}T${startTime}/${date}T${endTime}&details=${details}&location=${location}`;
    }

    /**
     * Generate WhatsApp message for booking.
     */
    function generateWhatsAppMessage(receiptUrl) {
        const serviceInfo = service.value || service;
        const f = form.value;

        // Format the date
        const bookingDate = new Date(f.booking_date);
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const dayName = dayNames[bookingDate.getDay()];
        const day = bookingDate.getDate().toString().padStart(2, '0');
        const month = (bookingDate.getMonth() + 1).toString().padStart(2, '0');
        const year = bookingDate.getFullYear().toString().slice(-2);
        const formattedDate = `${dayName} ${day}/${month}/${year}`;

        // Extract time from time slot
        const timeSlot = f.booking_time_slot ? f.booking_time_slot.split('-')[0] : '';

        const message = `*New booking*
*Service:*
${serviceInfo.name}
${serviceInfo.formatted_price}

*Time:*
${formattedDate}
${timeSlot}

*Customer Info*
Name: ${f.customer_name}
WhatsApp: ${f.customer_phone}
Instagram: ${f.customer_instagram || '-'}

Tunggu konfirmasi dari tim admin kami untuk ketersediaan waktu dan metode pembayaran.
Terimakasih`;

        return message;
    }

    /**
     * Submit booking.
     */
    async function submitBooking() {
        // Validate required fields
        if (!form.value.customer_name || !form.value.customer_phone) {
            errors.value = { general: 'Please fill in your name and WhatsApp number.' };
            return;
        }

        if (!form.value.booking_date || !form.value.booking_time_slot) {
            errors.value = { general: 'Please select a date and time for your booking.' };
            return;
        }

        processing.value = true;
        errors.value = {};

        try {
            const storeLink = tenant.value?.store_link;
            if (!storeLink) {
                throw new Error('Store link not available');
            }

            const serviceInfo = service.value || service;

            const response = await fetch(window.location.origin + '/' + storeLink + '/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    module_type: 'booking',
                    customer_name: form.value.customer_name,
                    customer_phone: form.value.customer_phone,
                    customer_instagram: form.value.customer_instagram,
                    service_id: serviceInfo.id,
                    booking_date: form.value.booking_date,
                    booking_time_slot: form.value.booking_time_slot,
                    booking_duration_min: serviceInfo.duration_min,
                    price: serviceInfo.price,
                    service_name: serviceInfo.name,
                }),
            });

            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to create booking');
            }

            // Get WhatsApp number
            const waNumber = tenant.value.formatted_whatsapp_number ||
                            (tenant.value.phone ? tenant.value.phone.replace(/[^0-9]/g, '') : null);

            if (!waNumber) {
                throw new Error('WhatsApp number not configured');
            }

            // Build message
            const message = generateWhatsAppMessage(result.receipt_url);
            const waLink = `https://wa.me/${waNumber}?text=${encodeURIComponent(message)}`;

            // Store receipt info
            lastOrderNumber.value = result.order_number;
            lastReceiptUrl.value = result.receipt_url;

            // Show success modal briefly, then redirect
            showBookingForm.value = false;
            showSuccessModal.value = true;
            setTimeout(() => {
                window.location.href = waLink;
            }, 1500);

            if (onBookingSuccess) {
                onBookingSuccess(result);
            }

        } catch (error) {
            console.error('Booking error:', error);
            errors.value = { general: error.message || 'Failed to process booking. Please try again.' };
        } finally {
            processing.value = false;
        }
    }

    function openBooking() {
        showBookingForm.value = true;
        form.value = {
            customer_name: '',
            customer_phone: '',
            customer_instagram: '',
            booking_date: '',
            booking_time_slot: '',
        };
        errors.value = {};
    }

    function closeBooking() {
        if (!processing.value) {
            showBookingForm.value = false;
            form.value = {
                customer_name: '',
                customer_phone: '',
                customer_instagram: '',
                booking_date: '',
                booking_time_slot: '',
            };
            errors.value = {};
        }
    }

    function closeSuccessModal() {
        showSuccessModal.value = false;
        lastOrderNumber.value = null;
        lastReceiptUrl.value = null;
    }

    return {
        // State
        showBookingForm,
        showSuccessModal,
        lastOrderNumber,
        lastReceiptUrl,
        processing,
        errors,
        form,
        // Actions
        generateTimeSlots,
        getMinDate,
        generateGoogleCalendarLink,
        submitBooking,
        openBooking,
        closeBooking,
        closeSuccessModal,
    };
}
