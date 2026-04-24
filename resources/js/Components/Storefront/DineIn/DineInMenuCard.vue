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
});
</script>

<template>
    <div class="flex flex-col">
        <!-- Product Image -->
        <Link :href="route('storefront.products.show', { store_link: storeLink, productSlug: product.slug })" class="block">
            <div v-if="product.first_image" class="aspect-square bg-gray-50 rounded-xl overflow-hidden mb-3">
                <img
                    :src="product.first_image"
                    :alt="product.title"
                    class="w-full h-full object-contain p-2"
                    loading="lazy"
                />
            </div>
            <div v-else class="aspect-square bg-gray-50 rounded-xl flex items-center justify-center mb-3">
                <svg class="h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </Link>

        <!-- Product Info (Display Only, No Add Button) -->
        <div class="flex-1 min-w-0">
            <Link :href="route('storefront.products.show', { store_link: storeLink, productSlug: product.slug })">
                <h3 class="font-semibold text-black text-base leading-snug line-clamp-2">{{ product.title }}</h3>
            </Link>
            <p class="text-gray-500 text-sm mt-0.5">{{ product.formatted_price }}</p>
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
