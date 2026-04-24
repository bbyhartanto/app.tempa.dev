<script setup>
import { ref, computed, watch } from 'vue';
import ServiceCard from '@/Components/Services/ServiceCard.vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    service: {
        type: Object,
        required: true,
    },
    tenant: {
        type: Object,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
    processing: {
        type: Boolean,
        default: false,
    },
    timeSlots: {
        type: Array,
        default: () => [],
    },
    minDate: {
        type: String,
        default: '',
    },
    googleCalendarLink: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['close', 'submit', 'date-change', 'time-change']);

// Date selection mode: 'today', 'tomorrow', or 'custom'
const dateMode = ref('today');
const showCustomCalendar = ref(false);

// Time picker state
const selectedHour = ref('');
const selectedMinute = ref('');

// Helper to format date to YYYY-MM-DD
function formatDate(date) {
    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, '0');
    const day = date.getDate().toString().padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Initialize with today
function initializeDate() {
    const today = new Date();
    dateMode.value = 'today';
    showCustomCalendar.value = false;
    emit('date-change', formatDate(today));
}

// Watch for modal open to initialize date
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        initializeDate();
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}, { immediate: true });

// Handle date mode selection
function selectDateMode(mode) {
    dateMode.value = mode;
    
    if (mode === 'today') {
        const today = new Date();
        emit('date-change', formatDate(today));
        showCustomCalendar.value = false;
    } else if (mode === 'tomorrow') {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        emit('date-change', formatDate(tomorrow));
        showCustomCalendar.value = false;
    } else if (mode === 'custom') {
        showCustomCalendar.value = true;
    }
}

// Handle custom date selection
function handleCustomDateChange(event) {
    emit('date-change', event.target.value);
}

// Generate hours (08-21)
const hours = Array.from({ length: 14 }, (_, i) => (i + 8).toString().padStart(2, '0'));
const minutes = Array.from({ length: 60 }, (_, i) => i.toString().padStart(2, '0'));

// Watch for time changes
watch([selectedHour, selectedMinute], ([newHour, newMinute]) => {
    // Only emit if both values are selected
    if (newHour && newMinute) {
        const timeStr = `${newHour}:${newMinute}`;
        emit('time-change', timeStr);
    }
});

// Watch for modal open to reset time
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        // Reset time to empty
        selectedHour.value = '';
        selectedMinute.value = '';
    }
});

// Computed for formatted display date
const displayDate = computed(() => {
    if (!props.form.booking_date) return '';
    const date = new Date(props.form.booking_date);
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
});

// Computed for formatted display time
const displayTime = computed(() => {
    if (!props.form.booking_time_slot) return '';
    return props.form.booking_time_slot.split('-')[0];
});

// Lock body scroll when modal is open
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}, { immediate: true });

function close() {
    if (!props.processing) {
        emit('close');
    }
}

function submit() {
    emit('submit');
}

function formatCurrency(amount, currency = 'IDR') {
    if (currency.toUpperCase() === 'IDR') {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
    return currency + ' ' + Number(amount).toFixed(2);
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex flex-col bg-white">
            <!-- Header -->
            <header class="flex-shrink-0 px-4 pt-6 pb-4 border-b border-gray-200">
                <div class="flex items-start">
                    <button @click="close" class="flex-shrink-0 pt-1 pr-3">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-black">Booking</h2>
                        <p class="text-gray-500 text-base mt-1">{{ service.name }}</p>
                    </div>
                </div>
            </header>

            <!-- Form Content -->
            <form @submit.prevent="submit" class="flex-1 overflow-y-auto px-4 py-6" style="overscroll-behavior: contain;" @touchmove.stop>
                <!-- General Error -->
                <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errors.general }}</p>
                </div>

                <!-- Date Selection -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-black mb-3">Select Date</h3>
                    
                    <!-- Date Mode Pills -->
                    <div class="flex gap-2 mb-3">
                        <button
                            type="button"
                            @click="selectDateMode('today')"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-full border transition',
                                dateMode === 'today'
                                    ? 'bg-black text-white border-black'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400'
                            ]"
                        >
                            Today
                        </button>
                        <button
                            type="button"
                            @click="selectDateMode('tomorrow')"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-full border transition',
                                dateMode === 'tomorrow'
                                    ? 'bg-black text-white border-black'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400'
                            ]"
                        >
                            Tomorrow
                        </button>
                        <button
                            type="button"
                            @click="selectDateMode('custom')"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-full border transition',
                                dateMode === 'custom'
                                    ? 'bg-black text-white border-black'
                                    : 'bg-white text-gray-700 border-gray-200 hover:border-gray-400'
                            ]"
                        >
                            Custom
                            <span v-if="displayDate && dateMode === 'custom'" class="ml-1">({{ displayDate }})</span>
                        </button>
                    </div>

                    <!-- Custom Calendar (shown when 'Custom' is selected) -->
                    <div v-if="showCustomCalendar" class="mt-3">
                        <input
                            type="date"
                            :value="form.booking_date"
                            @input="handleCustomDateChange"
                            :min="minDate.split('T')[0]"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-black focus:outline-none text-base"
                        />
                    </div>

                    <!-- Selected Date Display -->
                    <div v-if="form.booking_date" class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Selected Date:</p>
                        <p class="text-base font-semibold text-black">{{ displayDate }}</p>
                    </div>
                </div>

                <!-- Time Selection -->
                <div class="mb-6">
                    
                    <!-- Time Picker -->
                    <div class="flex items-center gap-3">
                        <!-- Hour Selector -->
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Hour</label>
                            <select
                                v-model="selectedHour"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-black focus:outline-none text-base"
                            >
                                <option value="" disabled>--</option>
                                <option v-for="hour in hours" :key="hour" :value="hour">
                                    {{ hour }}
                                </option>
                            </select>
                        </div>

                        <!-- Separator -->
                        <div class="text-2xl font-bold text-black pt-6">:</div>

                        <!-- Minute Selector -->
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Minute</label>
                            <select
                                v-model="selectedMinute"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-black focus:outline-none text-base"
                            >
                                <option value="" disabled>--</option>
                                <option v-for="minute in minutes" :key="minute" :value="minute">
                                    {{ minute }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Selected Time Display -->
                    <div v-if="selectedHour && selectedMinute" class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-500">Selected Time:</p>
                        <p class="text-base font-semibold text-black">{{ selectedHour }}:{{ selectedMinute }}</p>
                    </div>
                </div>

                <!-- Selected Service -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-black mb-3">Selected Service</h3>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <ServiceCard
                            :service="service"
                            size="small"
                        />
                    </div>
                </div>
                <!-- Customer Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-black mb-4">Your Information</h3>

                    <!-- Name -->
                    <div class="mb-4">
                        <input
                            v-model="form.customer_name"
                            type="text"
                            required
                            class="w-full px-0 py-3 border-b border-gray-200 focus:border-black focus:outline-none text-base placeholder-gray-400"
                            placeholder="Nama pemesan"
                        />
                        <p v-if="errors.customer_name" class="mt-1 text-sm text-red-600">{{ errors.customer_name }}</p>
                    </div>

                    <!-- WhatsApp -->
                    <div class="mb-4">
                        <input
                            v-model="form.customer_phone"
                            type="tel"
                            required
                            class="w-full px-0 py-3 border-b border-gray-200 focus:border-black focus:outline-none text-base placeholder-gray-400"
                            placeholder="No Whatsapp"
                        />
                        <p v-if="errors.customer_phone" class="mt-1 text-sm text-red-600">{{ errors.customer_phone }}</p>
                    </div>

                    <!-- Instagram -->
                    <div>
                        <input
                            v-model="form.customer_instagram"
                            type="text"
                            class="w-full px-0 py-3 border-b border-gray-200 focus:border-black focus:outline-none text-base placeholder-gray-400"
                            placeholder="Instagram Username (optional)"
                        />
                    </div>
                </div>

                <!-- Payment Note -->
                <div class="mb-6">
                    <p class="text-black text-sm leading-relaxed">
                        <span class="font-semibold">Note:</span>
                        Payment will be handled directly with the admin via WhatsApp.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pb-8 safe-area-pb">
                    <button
                        type="submit"
                        :disabled="processing || !form.booking_date || !form.booking_time_slot || !form.customer_name || !form.customer_phone"
                        class="w-full py-4 bg-green-600 text-white font-bold text-lg rounded-full hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 transition"
                    >
                        <svg v-if="processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span v-if="processing">Processing...</span>
                        <span v-else>Book & Chat Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>

<style>
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
