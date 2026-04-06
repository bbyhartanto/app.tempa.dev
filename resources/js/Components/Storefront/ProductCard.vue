<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    storeLink: {
        type: String,
        required: true,
    },
    showPrice: {
        type: Boolean,
        default: true,
    },
    primaryColor: {
        type: String,
        default: '#3B82F6',
    },
});

defineEmits(['add-to-cart']);
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden flex flex-col">
        <!-- Product Image -->
        <Link :href="route('storefront.products.show', { store_link: storeLink, productSlug: product.slug })" class="block">
            <div v-if="product.first_image" class="aspect-square bg-gray-100">
                <img 
                    :src="product.first_image" 
                    :alt="product.title" 
                    class="w-full h-full object-cover" 
                    loading="lazy"
                />
            </div>
            <div v-else class="aspect-square bg-gray-100 flex items-center justify-center">
                <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </Link>

        <!-- Product Info -->
        <div class="p-3 flex flex-col flex-1">
            <Link :href="route('storefront.products.show', { store_link: storeLink, productSlug: product.slug })">
                <h3 class="font-medium text-gray-900 text-sm line-clamp-2">{{ product.title }}</h3>
            </Link>

            <div v-if="showPrice" class="mt-1">
                <p class="text-lg font-bold" :style="{ color: primaryColor }">
                    {{ product.formatted_price }}
                </p>
            </div>

            <!-- Add to Cart Button -->
            <button
                @click="$emit('add-to-cart', product)"
                class="mt-2 w-full py-2 px-3 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-800 active:bg-gray-700 transition"
            >
                Add to Cart
            </button>
        </div>
    </div>
</template>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
