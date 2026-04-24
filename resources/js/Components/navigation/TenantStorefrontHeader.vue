<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    backUrl: {
        type: String,
        default: '/',
    },
});
</script>

<template>
    <header class="bg-white shadow-sm px-4 py-3 sticky top-0 z-10">
        <div class="flex items-center space-x-3">
            <!-- Back Button -->
            <a
                :href="backUrl"
                class="text-gray-600 hover:text-gray-900"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-lg font-bold text-gray-900">{{ title }}</h1>
        </div>
    </header>
</template>
