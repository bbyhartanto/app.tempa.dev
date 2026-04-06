<script setup>
import CheckoutForm from '@/Components/CheckoutForm.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    cartItems: {
        type: Array,
        required: true,
    },
    tenant: {
        type: Object,
        required: true,
    },
    orderNumber: {
        type: String,
        default: null,
    },
    receiptUrl: {
        type: String,
        default: null,
    },
    showSuccess: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit', 'close-success']);

function handleSubmit(data) {
    emit('submit', data);
}

function handleClose() {
    emit('close');
}

function handleCloseSuccess() {
    emit('close-success');
}
</script>

<template>
    <!-- Checkout Form -->
    <CheckoutForm
        :show="show && !showSuccess"
        :cart-items="cartItems"
        :tenant="tenant"
        @close="handleClose"
        @submit="handleSubmit"
    />

    <!-- Success Modal -->
    <Teleport to="body">
        <div v-if="showSuccess" class="fixed inset-0 z-50 flex flex-col bg-white">
            <!-- Header -->
            <header class="flex-shrink-0 px-4 pt-6 pb-4 border-b border-gray-200">
                <div class="flex items-start">
                    <button @click="handleCloseSuccess" class="flex-shrink-0 pt-1 pr-3">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-black">Order Success</h2>
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

                    <h3 class="text-xl font-bold text-black mb-2">Order Placed Successfully!</h3>
                    <p class="text-gray-500 mb-8">
                        Your order has been recorded. You can view your receipt anytime using the link below.
                    </p>

                    <!-- Order Number -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-6">
                        <p class="text-sm text-gray-500 mb-1">Order Number:</p>
                        <p class="text-2xl font-bold text-black">{{ orderNumber }}</p>
                    </div>

                    <!-- View Receipt Button -->
                    <a
                        :href="receiptUrl"
                        target="_blank"
                        class="block w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-full hover:bg-blue-700 transition"
                    >
                        View Receipt
                    </a>
                </div>
            </div>
        </div>
    </Teleport>
</template>
