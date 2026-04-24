<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useBooking } from '@/Composables/useBooking';
import BookingModal from '@/Components/Storefront/BookingModal.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    service: {
        type: Object,
        required: true,
    },
    templateConfig: {
        type: Object,
        default: () => ({}),
    },
});

const tenantRef = ref(props.tenant);
const serviceRef = ref(props.service);

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
} = useBooking(tenantRef, serviceRef);

const timeSlots = ref([]);
const googleCalendarLink = ref(null);

// Watch for date changes to regenerate time slots
watch(() => form.value.booking_date, (newDate) => {
    if (newDate) {
        timeSlots.value = generateTimeSlots(newDate);
        form.value.booking_time_slot = ''; // Reset time selection
    } else {
        timeSlots.value = [];
    }
});

// Watch for time selection to update Google Calendar link
watch(() => form.value.booking_time_slot, (newTime) => {
    googleCalendarLink.value = generateGoogleCalendarLink(form.value.booking_date, newTime);
});

function handleDateChange(date) {
    form.value.booking_date = date;
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
    <Head :title="service.name" />

    <div class="min-h-screen bg-white">
        <!-- Header -->
        <header class="sticky top-0 z-40 bg-white border-b border-gray-200">
            <div class="max-w-3xl mx-auto px-4 py-4 flex items-center">
                <a :href="route('storefront.catalog', tenant.store_link)" class="mr-4">
                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-xl font-bold">{{ tenant.name }}</h1>
            </div>
        </header>

        <!-- Service Details -->
        <main class="max-w-3xl mx-auto px-4 py-6">
            <!-- Service Image -->
            <div v-if="service.first_image" class="mb-6 rounded-xl overflow-hidden">
                <img :src="service.first_image" :alt="service.name" class="w-full h-64 object-cover" />
            </div>

            <!-- Service Info -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold mb-2">{{ service.name }}</h2>
                <p class="text-2xl font-bold text-green-600 mb-4">{{ service.formatted_price }}</p>

                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center space-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ service.duration_min }} minutes</span>
                    </div>
                    <div v-if="service.buffer_min" class="flex items-center space-x-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m3 4h.01M12 2a10 10 0 110 20 10 10 0 010-20z" />
                        </svg>
                        <span>+{{ service.buffer_min }} min buffer</span>
                    </div>
                </div>

                <p v-if="service.description" class="text-gray-700 leading-relaxed whitespace-pre-line">
                    {{ service.description }}
                </p>
            </div>

            <!-- Book Now Button -->
            <button
                @click="openBooking"
                class="w-full py-4 bg-green-600 text-white font-bold text-lg rounded-full hover:bg-green-700 transition flex items-center justify-center space-x-2"
            >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                <span>Book via WhatsApp</span>
            </button>
        </main>

        <!-- Booking Modal -->
        <BookingModal
            :show="showBookingForm"
            :service="service"
            :tenant="tenant"
            :form="form"
            :errors="errors"
            :processing="processing"
            :time-slots="timeSlots"
            :min-date="getMinDate()"
            :google-calendar-link="googleCalendarLink"
            @close="handleCloseBooking"
            @date-change="handleDateChange"
            @submit="handleSubmitBooking"
        />

        <!-- Success Modal -->
        <Teleport to="body">
            <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex flex-col bg-white">
                <header class="flex-shrink-0 px-4 pt-6 pb-4 border-b border-gray-200">
                    <div class="flex items-start">
                        <button @click="handleCloseSuccess" class="flex-shrink-0 pt-1 pr-3">
                            <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-2xl font-bold text-black">Booking Success</h2>
                        </div>
                    </div>
                </header>

                <div class="flex-1 overflow-y-auto px-4 py-8">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                            <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>

                        <h3 class="text-xl font-bold text-black mb-2">Booking Placed Successfully!</h3>
                        <p class="text-gray-500 mb-8">
                            Your booking has been recorded. You can view your booking details anytime using the link below.
                        </p>

                        <div class="bg-gray-50 rounded-xl p-6 mb-6">
                            <p class="text-sm text-gray-500 mb-1">Booking Number:</p>
                            <p class="text-2xl font-bold text-black">{{ lastOrderNumber }}</p>
                        </div>

                        <a
                            :href="lastReceiptUrl"
                            target="_blank"
                            class="block w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-full hover:bg-blue-700 transition"
                        >
                            View Booking Details
                        </a>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
