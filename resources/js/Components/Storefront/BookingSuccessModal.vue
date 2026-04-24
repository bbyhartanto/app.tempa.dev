<script setup>
const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    orderNumber: {
        type: String,
        default: '',
    },
    receiptUrl: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

function close() {
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex flex-col bg-white">
            <!-- Header -->
            <header class="flex-shrink-0 px-4 pt-6 pb-4 border-b border-gray-200">
                <div class="flex items-start">
                    <button @click="close" class="flex-shrink-0 pt-1 pr-3">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-black">Booking Success</h2>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-4 py-8">
                <div class="text-center">
                    <!-- Success Icon -->
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
                        <svg class="h-12 w-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <!-- Success Message -->
                    <h3 class="text-xl font-bold text-black mb-2">Booking Placed Successfully!</h3>
                    <p class="text-gray-500 mb-8">
                        Your booking has been recorded. View details below.
                    </p>

                    <!-- Booking Number -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6">
                        <p class="text-sm text-gray-500 mb-1">Booking Number:</p>
                        <p class="text-2xl font-bold text-black">{{ orderNumber }}</p>
                    </div>

                    <!-- View Details Button -->
                    <a
                        v-if="receiptUrl"
                        :href="receiptUrl"
                        target="_blank"
                        class="block w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-full hover:bg-blue-700 transition"
                    >
                        View Booking Details
                    </a>
                </div>
            </div>
        </div>
    </Teleport>
</template>
