<template>
    <span
        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
        :class="statusClass(status)"
    >
        {{ statusLabel(status) }}
    </span>
</template>

<script setup>
import { statusClass, statusLabel } from '@/utils/statusBadge';

defineProps({
    status: {
        type: String,
        required: true,
    },
});
</script>
