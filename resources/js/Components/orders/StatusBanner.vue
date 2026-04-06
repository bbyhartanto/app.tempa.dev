<template>
    <div class="flex items-center justify-center gap-2 mt-3 mb-6">
        <span
            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
            :class="statusClass(order.status)"
        >
            {{ statusLabel(order.status) }}
        </span>
    </div>
</template>

<script setup>
import { statusClass, statusLabel } from '@/utils/statusBadge';

defineProps({
    order: {
        type: Object,
        required: true,
    },
});
</script>
