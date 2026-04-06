<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>

        <div class="space-y-4">
            <ReceiptItemRow
                v-for="item in items"
                :key="item.id"
                :item="item"
            />
        </div>

        <PricingSummary :order="order" :show-payment-status="true" />
    </div>
</template>

<script setup>
import ReceiptItemRow from './ReceiptItemRow.vue';
import PricingSummary from './PricingSummary.vue';

defineProps({
    items: {
        type: Array,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
});
</script>
