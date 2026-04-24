<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CatalogModule from '@/Components/modules/CatalogModule.vue';
import BookingModule from '@/Components/modules/BookingModule.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
        default: () => [],
    },
    services: {
        type: Array,
        required: true,
        default: () => [],
    },
    totalServices: {
        type: Number,
        required: true,
        default: 0,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

const enabledModules = props.tenant.modules || ['catalog'];
const storeLink = props.tenant.store_link;
const hasDineIn = enabledModules.includes('dine_in');
</script>

<template>
    <Head :title="tenant.name" />

    <div class="min-h-screen bg-white">
        <!-- Header Section with Yellow Background -->
        <header class="bg-[#FFC947] px-5 py-8 pb-12">
            <div class="max-w-md mx-auto">
                <!-- Store Info -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold text-black leading-tight mb-2">
                            {{ tenant.name }}
                        </h1>
                        <div class="flex items-center text-black text-base">
                            <span>Bandung</span>
                            <a href="#" class="ml-2 underline">maps</a>
                            <span class="ml-1">📍</span>
                        </div>
                    </div>

                    <!-- Logo -->
                    <div v-if="tenant.logo_url" class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 ml-4">
                        <img :src="tenant.logo_url" :alt="tenant.name" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 ml-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>

                <!-- Description -->
                <p v-if="tenant.description" class="text-black text-base leading-relaxed mb-8">
                    {{ tenant.description }}
                </p>

                <!-- External Links -->
                <div class="space-y-3">
                    <template v-if="tenant.store_links && tenant.store_links.length > 0">
                        <a
                            v-for="link in tenant.store_links"
                            :key="link.label"
                            :href="link.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">{{ link.label }}</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                    </template>

                    <template v-else>
                        <a
                            href="#"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">Grabfood link</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                        <a
                            href="#"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">Gofood link</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                    </template>

                    <!-- Dine-In Menu Link (only if enabled) -->
                    <Link
                        v-if="hasDineIn"
                        :href="route('storefront.dine-in', { store_link: storeLink })"
                        class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-black text-lg">🍽️ Menu Dine In</span>
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Module: Catalog (Products) -->
        <CatalogModule
            v-if="enabledModules.includes('catalog')"
            :products="products"
            :tenant="tenant"
        />

        <!-- Module: Booking (Services) -->
        <BookingModule
            v-if="enabledModules.includes('booking')"
            :services="services"
            :total-services="totalServices"
            :tenant="tenant"
        />
    </div>
</template>
