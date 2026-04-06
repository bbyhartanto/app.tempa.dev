<script setup>
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    cartItems: {
        type: Array,
        required: true,
    },
    cartTotal: {
        type: Number,
        required: true,
    },
    cartCount: {
        type: Number,
        required: true,
    },
    currency: {
        type: String,
        default: 'IDR',
    },
});

const emit = defineEmits(['close', 'remove-item', 'update-quantity', 'checkout']);

// Lock body scroll when drawer is open
watch(() => props.show, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}, { immediate: true });

function handleRemoveItem(productId) {
    emit('remove-item', productId);
}

function handleUpdateQuantity(productId, quantity) {
    emit('update-quantity', { productId, quantity });
}

function handleCheckout() {
    emit('checkout');
}

function handleClose() {
    emit('close');
}

function formatCurrency(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-50 flex flex-col bg-white">
            <!-- Header -->
            <header class="flex-shrink-0 px-4 pt-6 pb-4 border-b border-gray-200">
                <div class="flex items-start">
                    <button @click="handleClose" class="flex-shrink-0 pt-1 pr-3">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div>
                        <h2 class="text-2xl font-bold text-black">Shopping cart</h2>
                        <p class="text-gray-500 text-sm mt-0.5">{{ cartCount }} Items</p>
                    </div>
                </div>
            </header>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto px-4" style="overscroll-behavior: contain;" @touchmove.stop>
                <div v-if="cartItems.length === 0" class="text-center text-gray-500 py-16">
                    Your cart is empty
                </div>

                <div v-else>
                    <div v-for="item in cartItems" :key="item.id" class="py-4 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 pr-4">
                                <h4 class="font-medium text-black text-base leading-snug">{{ item.title }}</h4>
                                <p class="text-gray-500 text-sm mt-1">{{ item.formatted_price }}</p>
                            </div>

                            <div class="flex items-center space-x-3">
                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="handleUpdateQuantity(item.id, item.quantity - 1)"
                                        class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300 active:bg-gray-400 transition"
                                    >
                                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="w-6 text-center text-black font-medium">{{ item.quantity }}</span>
                                    <button
                                        @click="handleUpdateQuantity(item.id, item.quantity + 1)"
                                        class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center hover:bg-gray-300 active:bg-gray-400 transition"
                                    >
                                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Delete Button -->
                                <button @click="handleRemoveItem(item.id)" class="text-black hover:text-red-500 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer v-if="cartItems.length > 0" class="flex-shrink-0 border-t border-gray-200">
                <!-- Summary -->
                <div class="px-4 py-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-black text-base">Subtotal</span>
                        <span class="text-black font-bold text-lg">{{ formatCurrency(cartTotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black text-base">Tax</span>
                        <span class="text-black text-base">-</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-black text-base">Ongkos Kirim</span>
                        <span class="text-black text-base">-</span>
                    </div>
                </div>

                <!-- Note -->
                <div class="px-4 pb-4">
                    <p class="text-black text-sm leading-relaxed">
                        <span class="font-semibold">Catatan:</span>
                        Pembayaran dilakukan melalui chat langsung dengan admin dan kirim bukti transfer melalui chat
                    </p>
                </div>

                <!-- Checkout Button -->
                <div class="px-4 pb-6 safe-area-pb">
                    <button
                        @click="handleCheckout"
                        class="w-full py-4 bg-black text-white font-bold text-lg rounded-full hover:bg-gray-800 active:bg-gray-900 transition flex items-center justify-center space-x-2"
                    >
                        <span>Checkout via chat admin</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </button>
                </div>
            </footer>
        </div>
    </Teleport>
</template>

<style>
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
