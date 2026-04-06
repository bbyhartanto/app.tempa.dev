<template>
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <Link href="/dashboard/orders" class="text-sm text-blue-600 hover:text-blue-700 mb-1 block">
                        ← Back to Orders
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">{{ order.order_number }}</h1>
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

defineProps({
    order: {
        type: Object,
        required: true,
    },
});
</script>
