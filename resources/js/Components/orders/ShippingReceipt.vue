<template>
    <div v-if="path" class="mt-4 pt-4 border-t border-dashed border-gray-300">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Shipping Receipt</h3>
        <img
            :src="imageUrl"
            alt="Shipping Receipt"
            class="w-full rounded-lg"
        />
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    path: {
        type: String,
        required: true,
    },
});

const imageUrl = computed(() => `/storage/${props.path}`);
</script>
