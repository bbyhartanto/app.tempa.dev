<script setup>
import { computed } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const state = computed(() => {
    if (props.item.is_removed) return 'removed';
    if (props.item.original_quantity !== props.item.current_quantity) return 'adjusted';
    return 'ok';
});

const config = {
    ok:       { color: 'text-green-600', path: 'M5 13l4 4L19 7' },
    adjusted: { color: 'text-orange-600', path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
    removed:  { color: 'text-red-600', path: 'M6 18L18 6M6 6l12 12' },
};
</script>

<template>
    <svg class="w-5 h-5" :class="config[state].color" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="config[state].path" />
    </svg>
</template>
