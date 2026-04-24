<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import DineInMenuCard from './DineInMenuCard.vue';

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

const emit = defineEmits(['load-more']);

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
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <p class="text-gray-500">No menu items available.</p>
        </div>

        <!-- Products Grid -->
        <div v-else class="grid grid-cols-2 gap-x-4 gap-y-6">
            <DineInMenuCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                :store-link="storeLink"
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
