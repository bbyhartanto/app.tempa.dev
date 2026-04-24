<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    tenant: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['addToCart']);

const isGlass = computed(() => props.tenant?.settings?.button_glass_effect || false);

function handleAddToCart(product) {
    emit('addToCart', product);
}
</script>

<template>
    <section :class="['max-w-md mx-auto px-5 py-8 -mt-6 rounded-t-3xl', isGlass ? 'glassify border-none' : 'bg-white']">
        <h2 class="text-2xl font-bold text-black mb-6">Products</h2>

        <div v-if="products.length === 0" class="text-center py-12">
            <p class="text-gray-500">No products available yet.</p>
        </div>

        <div v-else class="grid grid-cols-2 gap-x-4 gap-y-6">
            <div v-for="product in products" :key="product.id" class="flex flex-col">
                <!-- Product Image -->
                <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })" class="block">
                    <div v-if="product.first_image" class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                        <img :src="product.first_image" :alt="product.title" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </Link>

                <!-- Product Info -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0">
                        <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })">
                            <h3 class="font-medium text-black text-sm leading-snug line-clamp-2">{{ product.title }}</h3>
                        </Link>
                        <p class="text-gray-500 text-sm mt-1">{{ product.formatted_price }}</p>
                    </div>
                    
                    <!-- Add Button -->
                    <button
                        @click="handleAddToCart(product)"
                        class="flex-shrink-0 w-10 h-10 bg-[#FF8C42] rounded-full flex items-center justify-center hover:bg-[#FF9D5C] active:bg-[#FF7A33] transition"
                    >
                        <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- View All Products Link -->
        <div v-if="products.length > 0" class="text-center mt-8">
            <Link 
                :href="route('storefront.catalog', { store_link: tenant.store_link })"
                class="text-black text-lg underline font-medium hover:opacity-70 transition"
            >
                Lihat semua produk
            </Link>
        </div>
    </section>
</template>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
