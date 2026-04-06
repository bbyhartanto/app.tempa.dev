<script setup>
const props = defineProps({
    cartCount: {
        type: Number,
        default: 0,
    },
});

defineEmits(['click']);
</script>

<template>
    <div class="fixed bottom-4 left-0 right-0 z-50 px-4 safe-area-pb">
        <div class="max-w-md mx-auto mb-4">
            <button
                @click="$emit('click')"
                class="w-full py-4 bg-black text-white font-bold text-lg rounded-full hover:bg-gray-800 active:bg-gray-900 transition flex items-center justify-between px-6 shadow-lg relative"
            >
                <span>Whatsapp Cart</span>
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span 
                        v-if="cartCount > 0" 
                        class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"
                    >
                        {{ cartCount > 99 ? '99+' : cartCount }}
                    </span>
                </div>
            </button>
        </div>
    </div>
</template>

<style>
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 10);
}
</style>
