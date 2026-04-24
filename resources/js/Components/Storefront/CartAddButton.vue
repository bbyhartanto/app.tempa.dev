<script setup>
defineProps({
    product: {
        type: Object,
        required: true,
    },
});

defineEmits(['click']);
</script>

<template>
    <button
        @click="$emit('click', product)"
        class="flex-shrink-0 w-10 h-10 bg-[#FF8C42] rounded-full flex items-center justify-center hover:bg-[#FF9D5C] active:bg-[#FF7A33] transition"
        :aria-label="`Add ${product.title} to cart`"
    >
        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
    </button>
</template>
