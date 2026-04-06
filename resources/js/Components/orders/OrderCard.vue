<template>
    <div class="bg-gray-50 rounded-3xl p-6 mb-6">
        <!-- Order Items Header -->
        <h2 class="text-base font-semibold text-gray-900 mb-4">Order Items</h2>

        <!-- Items List -->
        <div class="space-y-4 mb-4">
            <ReceiptItemRow
                v-for="item in items"
                :key="item.id"
                :item="item"
            />
        </div>

        <!-- Pricing Summary -->
        <div class="border-t border-dashed border-gray-300 pt-4 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="font-semibold text-gray-900">{{ formatCurrency(order.adjusted_subtotal, currency) }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Ongkos Kirim</span>
                <span class="font-semibold text-gray-900">
                    {{ order.shipping_cost > 0 ? formatCurrency(order.shipping_cost, currency) : '-' }}
                </span>
            </div>

            <div class="border-t border-dashed border-gray-300 pt-3">
                <div class="flex justify-between">
                    <span class="text-base font-medium text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-gray-900">{{ formatCurrency(order.total, currency) }}</span>
                </div>
            </div>
        </div>

        <!-- Dynamic Payment Message -->
        <div class="mt-6 pt-4 border-t border-dashed border-gray-300">
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ paymentMessage }}</p>
        </div>

        <!-- CTA Button (only show if not paid) -->
        <div v-if="order.status !== 'paid'" class="mt-6">
            <button
                @click="$emit('confirm-payment')"
                class="w-full py-4 px-6 border-2 border-gray-900 text-gray-900 font-semibold rounded-full hover:bg-gray-900 hover:text-white transition-colors"
            >
                Confirm pembayaran ke Merchant
            </button>
        </div>

        <!-- Extras Slot (Adjustment Notes, Shipping Receipt) -->
        <slot name="extras" />
    </div>
</template>

<script setup>
import ReceiptItemRow from './ReceiptItemRow.vue';
import { formatCurrency } from '@/utils/currency';
import { getPaymentMessage } from '@/utils/statusMessages';
import { computed } from 'vue';

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
});

const currency = computed(() => props.items[0]?.currency || 'IDR');
const paymentMessage = computed(() => getPaymentMessage(props.order.status, props.order.payment_notes));

defineEmits(['confirm-payment']);
</script>
