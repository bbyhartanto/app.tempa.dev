<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ProductCardCatalog from './ProductCardCatalog.vue';

const page = usePage();
const isGlass = computed(() => page.props.tenant?.settings?.button_glass_effect || false);

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
    storeLink: {
        type: String,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    hasMore: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['add-to-cart', 'load-more']);

// Intersection Observer for infinite scroll
const sentinelRef = ref(null);
let observer = null;

onMounted(() => {
    if (!sentinelRef.value || !props.hasMore) return;

    observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !props.loading && props.hasMore) {
            emit('load-more');
        }
    }, {
        rootMargin: '200px',
    });

    observer.observe(sentinelRef.value);
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <div>
        <!-- Empty State -->
        <div v-if="products.length === 0 && !loading" class="text-center py-12">
            <p class="text-gray-500">No products available yet.</p>
        </div>

        <!-- Products Grid -->
        <div v-else :class="['grid grid-cols-2 gap-x-4 gap-y-6', isGlass ? 'glassify p-5 rounded-2xl border-none' : '']">
            <ProductCardCatalog
                v-for="product in products"
                :key="product.id"
                :product="product"
                :store-link="storeLink"
                @add-to-cart="$emit('add-to-cart', $event)"
            />
        </div>

        <!-- Loading Indicator -->
        <div v-if="loading" class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>

        <!-- Sentinel Element for Infinite Scroll -->
        <div v-if="hasMore" ref="sentinelRef" class="h-1"></div>

        <!-- End Message -->
        <div v-if="!hasMore && products.length > 0" class="text-center py-6 text-gray-500 text-sm">
            You've reached the end!
        </div>
    </div>
</template>
