<template>
    <div v-if="notes" class="mt-4 pt-4 border-t border-dashed border-gray-300">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Store Notes</h3>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ notes }}</p>
    </div>
</template>

<script setup>
defineProps({
    notes: {
        type: String,
        required: true,
    },
});
</script>
