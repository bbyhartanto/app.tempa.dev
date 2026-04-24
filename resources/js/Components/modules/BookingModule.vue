<script setup>
import { ref, toRef, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useBooking } from '@/Composables/useBooking';
import BookingModal from '@/Components/Storefront/BookingModal.vue';
import BookingSuccessModal from '@/Components/Storefront/BookingSuccessModal.vue';
import ServiceCard from '@/Components/Services/ServiceCard.vue';

const props = defineProps({
    services: {
        type: Array,
        required: true,
        default: () => [],
    },
    totalServices: {
        type: Number,
        required: true,
        default: 0,
    },
    tenant: {
        type: Object,
        required: true,
    },
});

const tenantRef = toRef(props, 'tenant');
const selectedService = ref(null);

const {
    showBookingForm,
    showSuccessModal,
    lastOrderNumber,
    lastReceiptUrl,
    processing,
    errors,
    form,
    generateTimeSlots,
    getMinDate,
    generateGoogleCalendarLink,
    submitBooking,
    openBooking,
    closeBooking,
    closeSuccessModal,
} = useBooking(tenantRef, selectedService);

const timeSlots = ref([]);
const googleCalendarLink = ref(null);

function handleBookNow(service) {
    selectedService.value = service;
    openBooking();
}

// Watch for date changes
watch(() => form.value.booking_date, (newDate) => {
    if (newDate && selectedService.value) {
        // Time will be handled separately now
        form.value.booking_time_slot = '';
    } else {
        form.value.booking_time_slot = '';
    }
});

// Watch for time slot changes to update Google Calendar link
watch(() => form.value.booking_time_slot, (newTime) => {
    googleCalendarLink.value = generateGoogleCalendarLink(form.value.booking_date, newTime);
});

function handleDateChange(date) {
    form.value.booking_date = date;
}

function handleTimeChange(time) {
    // Create a time slot string based on selected time and service duration
    if (time && selectedService.value) {
        const [hours, minutes] = time.split(':').map(Number);
        const startMinutes = hours * 60 + minutes;
        const duration = selectedService.value.duration_min || 60;
        const endMinutes = startMinutes + duration;
        
        const endH = Math.floor(endMinutes / 60).toString().padStart(2, '0');
        const endM = (endMinutes % 60).toString().padStart(2, '0');
        const startH = hours.toString().padStart(2, '0');
        const startM = minutes.toString().padStart(2, '0');
        
        form.value.booking_time_slot = `${startH}:${startM}-${endH}:${endM}`;
    }
}

function handleCloseBooking() {
    closeBooking();
}

function handleSubmitBooking() {
    submitBooking();
}

function handleCloseSuccess() {
    closeSuccessModal();
}
</script>

<template>
    <section>
        <!-- Services Grid -->
        <div class="max-w-md mx-auto px-5 py-8 -mt-6 bg-white rounded-t-3xl">
            <h2 class="text-2xl font-bold text-black mb-6">Services</h2>

            <div v-if="services.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500">No services available yet.</p>
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="service in services"
                    :key="service.id"
                    class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition"
                >
                    <!-- Service Card -->
                    <div class="flex-1">
                        <ServiceCard
                            :service="service"
                            size="small"
                        />
                    </div>

                    <!-- Book Now Button -->
                    <button
                        @click="handleBookNow(service)"
                        class="flex-shrink-0 px-4 py-2 bg-[#FF8C42] text-black text-sm font-medium rounded-full hover:bg-[#FF9D5C] active:bg-[#FF7A33] transition"
                    >
                        Book
                    </button>
                </div>
            </div>

            <!-- View All Link (only show when there are more services than displayed) -->
            <!-- DEBUG: {{ totalServices }} / {{ services.length }} -->
            <div v-if="totalServices > services.length" class="text-center mt-8">
                <Link
                    :href="route('storefront.catalog', { store_link: tenant.store_link })"
                    class="text-black text-lg underline font-medium hover:opacity-70 transition"
                >
                    Lihat semua layanan
                </Link>
            </div>
        </div>

        <!-- Booking Modal -->
        <BookingModal
            :show="showBookingForm"
            :service="selectedService || {}"
            :tenant="tenant"
            :form="form"
            :errors="errors"
            :processing="processing"
            :time-slots="timeSlots"
            :min-date="getMinDate()"
            :google-calendar-link="googleCalendarLink"
            @close="handleCloseBooking"
            @date-change="handleDateChange"
            @time-change="handleTimeChange"
            @submit="handleSubmitBooking"
        />

        <!-- Success Modal -->
        <BookingSuccessModal
            :show="showSuccessModal"
            :order-number="lastOrderNumber"
            :receipt-url="lastReceiptUrl"
            @close="handleCloseSuccess"
        />
    </section>
</template>
