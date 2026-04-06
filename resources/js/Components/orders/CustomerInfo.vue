<template>
    <div class="mb-6">
        <h2 class="text-base font-semibold text-gray-500 mb-4">Customer Information</h2>
        <div class="space-y-3">
            <div>
                <p class="text-sm text-gray-400">Name</p>
                <p class="text-sm text-gray-700">{{ order.customer_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Phone</p>
                <p class="text-sm text-gray-700">{{ order.customer_phone }}</p>
            </div>
            <div v-if="order.customer_address">
                <p class="text-sm text-gray-400">Address</p>
                <p class="text-sm text-gray-700">{{ order.customer_address }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    order: {
        type: Object,
        required: true,
    },
});
</script>
