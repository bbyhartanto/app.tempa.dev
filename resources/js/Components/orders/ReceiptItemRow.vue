<template>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <div class="flex items-baseline gap-2">
                <span class="text-sm font-medium text-gray-900">{{ item.current_quantity }}x</span>
                <span class="text-sm font-medium text-gray-900">{{ item.product_name }}</span>
            </div>
            <p class="text-sm text-gray-400 mt-0.5">{{ formatCurrency(item.unit_price, item.currency) }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm font-semibold text-gray-900">{{ item.formatted_subtotal }}</p>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from '@/utils/currency';

defineProps({
    item: {
        type: Object,
        required: true,
    },
});
</script>
