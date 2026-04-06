<template>
    <div class="border-t mt-6 pt-6 space-y-2">
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Original Subtotal</span>
            <span class="font-medium">{{ formatCurrency(order.original_subtotal, orderCurrency) }}</span>
        </div>

        <div v-if="showAdjusted" class="flex justify-between text-sm text-orange-600">
            <span>Adjusted Subtotal</span>
            <span class="font-medium">{{ formatCurrency(order.adjusted_subtotal, orderCurrency) }}</span>
        </div>

        <div v-if="showShipping" class="flex justify-between text-sm">
            <span class="text-gray-600">Shipping Cost</span>
            <span class="font-medium">{{ formatCurrency(order.shipping_cost, orderCurrency) }}</span>
        </div>

        <div class="border-t pt-2 mt-2">
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span>{{ formatCurrency(order.total, orderCurrency) }}</span>
            </div>
        </div>

        <div v-if="showPaymentStatus" class="flex justify-between text-sm pt-2">
            <span class="text-gray-600">Payment Status</span>
            <span
                class="font-medium"
                :class="order.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'"
            >
                {{ order.payment_status === 'paid' ? '✓ Paid' : '⏳ Unpaid' }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatCurrency } from '@/utils/currency';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    currency: {
        type: String,
        default: null,
    },
    showPaymentStatus: {
        type: Boolean,
        default: true,
    },
});

const orderCurrency = computed(() => props.currency || 'IDR');
const showAdjusted = computed(() => props.order.original_subtotal !== props.order.adjusted_subtotal);
const showShipping = computed(() => Number(props.order.shipping_cost) > 0);
</script>
