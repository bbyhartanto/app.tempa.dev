<template>
    <header class="text-center mb-4">
        <!-- Circled Logo -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-200 mb-4">
            <img v-if="tenant.logo_url" :src="tenant.logo_url" :alt="tenant.name" class="h-full w-full rounded-full object-cover" />
            <svg v-else class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>

        <!-- Store Name -->
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ tenant.name }}</h1>

        <!-- Order Number -->
        <p class="text-sm text-gray-600">#{{ order.order_number }}</p>
    </header>
</template>

<script setup>
defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
});
</script>
