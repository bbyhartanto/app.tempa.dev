<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    cartItems: {
        type: Array,
        required: true,
    },
    tenant: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'submit']);

const processing = ref(false);
const errors = ref({});

const form = ref({
    customer_name: '',
    customer_phone: '',
    customer_address: '',
});

const cartTotal = computed(() => {
    return props.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const cartCount = computed(() => {
    return props.cartItems.reduce((sum, item) => sum + item.quantity, 0);
});

// Lock body scroll when form is open
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}, { immediate: true });

function formatCurrency(amount, currency = 'IDR') {
    if (currency.toUpperCase() === 'IDR') {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
    return currency + ' ' + Number(amount).toFixed(2);
}

function close() {
    if (!processing.value) {
        emit('close');
        resetForm();
    }
}

function resetForm() {
    form.value = {
        customer_name: '',
        customer_phone: '',
        customer_address: '',
    };
    errors.value = {};
}

async function submit() {
    processing.value = true;
    errors.value = {};

    try {
        emit('submit', {
            customer_name: form.value.customer_name,
            customer_phone: form.value.customer_phone,
            customer_address: form.value.customer_address,
            cartItems: props.cartItems,
        });
    } catch (error) {
        console.error('Checkout error:', error);
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            errors.value = { general: error.message || 'Failed to process order. Please try again.' };
        }
    } finally {
        processing.value = false;
    }
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
                        <h2 class="text-2xl font-bold text-black">Checkout</h2>
                        <p class="text-gray-500 text-sm mt-0.5">{{ cartCount }} Items</p>
                    </div>
                </div>
            </header>

            <!-- Form Content -->
            <form @submit.prevent="submit" class="flex-1 overflow-y-auto px-4 py-6" style="overscroll-behavior: contain;" @touchmove.stop>
                <!-- General Error -->
                <div v-if="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ errors.general }}</p>
                </div>

                <!-- Customer Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-black mb-4">Customer information</h3>

                    <!-- Name -->
                    <div class="mb-4">
                        <input
                            v-model="form.customer_name"
                            type="text"
                            required
                            class="w-full px-0 py-3 border-b border-gray-200 focus:border-black focus:outline-none text-base placeholder-gray-400"
                            placeholder="Nama pemesan"
                        />
                        <p v-if="errors.customer_name" class="mt-1 text-sm text-red-600">{{ errors.customer_name }}</p>
                    </div>

                    <!-- WhatsApp -->
                    <div class="mb-4">
                        <input
                            v-model="form.customer_phone"
                            type="tel"
                            required
                            class="w-full px-0 py-3 border-b border-gray-200 focus:border-black focus:outline-none text-base placeholder-gray-400"
                            placeholder="No Whatsapp"
                        />
                        <p v-if="errors.customer_phone" class="mt-1 text-sm text-red-600">{{ errors.customer_phone }}</p>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-base text-gray-400 mb-2">Alamat Pengiriman:</label>
                        <textarea
                            v-model="form.customer_address"
                            rows="3"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-black focus:outline-none text-base placeholder-gray-400 resize-none"
                            placeholder="Jl. Bengawan solo no 34, Kecamatan coblong, Jakarta pusat 401133"
                        />
                    </div>
                </div>

                <!-- Order Summary -->
                <div v-if="cartItems.length > 0" class="mb-6">
                    <h3 class="text-lg font-bold text-black mb-3">Order summary</h3>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                        <div v-for="item in cartItems" :key="item.id" class="flex items-center justify-between">
                            <div class="flex items-center space-x-2 flex-1 min-w-0">
                                <span class="text-black font-medium">{{ item.quantity }}x</span>
                                <span class="text-black truncate">{{ item.title }}</span>
                            </div>
                            <span class="text-black font-medium ml-2">{{ formatCurrency(item.price * item.quantity, item.currency) }}</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-2">
                            <div class="flex items-center justify-between">
                                <span class="text-black font-bold text-lg">Total</span>
                                <span class="text-black font-bold text-lg">{{ formatCurrency(cartTotal, cartItems[0]?.currency) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="mb-6">
                    <p class="text-black text-sm leading-relaxed">
                        <span class="font-semibold">Catatan:</span>
                        Pembayaran dilakukan melalui chat langsung dengan admin dan kirim bukti transfer melalui chat
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="pb-8 safe-area-pb">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full py-4 bg-green-600 text-white font-bold text-lg rounded-full hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 transition"
                    >
                        <svg v-if="processing" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span v-if="processing">Processing...</span>
                        <span v-else>Chat admin</span>
                    </button>
                </div>
            </form>
        </div>
    </Teleport>
</template>

<style>
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
