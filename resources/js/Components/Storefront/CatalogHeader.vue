<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const isGlass = computed(() => page.props.tenant?.settings?.button_glass_effect || false);

const props = defineProps({
    storeName: {
        type: String,
        required: true,
    },
    productCount: {
        type: Number,
        default: 0,
    },
    storeLink: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <header :class="['px-4 py-4 sticky top-0 z-40', isGlass ? 'glassify border-none' : 'bg-white']">
        <div class="flex items-start space-x-4">
            <!-- Back Button -->
            <Link :href="route('storefront.home', { store_link: storeLink })" class="flex-shrink-0 pt-1">
                <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </Link>

            <!-- Store Info -->
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-black">{{ storeName }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ productCount }} products</p>
            </div>
        </div>
    </header>
</template>
