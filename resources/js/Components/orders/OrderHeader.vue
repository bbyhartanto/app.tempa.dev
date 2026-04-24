<template>
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <Link href="/dashboard/orders" class="text-sm text-blue-600 hover:text-blue-700 mb-1 block">
                        ← Back to Orders
                    </Link>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
                        <!-- Booking Badge -->
                        <span
                            v-if="order.module_type === 'booking'"
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
                        >
                            📅 Booking
                        </span>
                    </div>
                    <!-- Booking Details -->
                    <div v-if="order.module_type === 'booking'" class="mt-2 text-sm text-gray-600">
                        <span v-if="order.booking_date">{{ formatBookingDate(order.booking_date) }}</span>
                        <span v-if="order.booking_time_slot"> • {{ order.booking_time_slot }}</span>
                        <span v-if="order.booking_duration_min"> ({{ order.booking_duration_min }} min)</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                        :class="statusClass(order.status)"
                    >
                        {{ statusLabel(order.status) }}
                    </span>
                    <span
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                        :class="paymentStatusClass(order.payment_status)"
                    >
                        {{ paymentStatusLabel(order.payment_status) }}
                    </span>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { statusClass, statusLabel, paymentStatusClass, paymentStatusLabel } from '@/utils/statusBadge';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

function formatBookingDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}
</script>
