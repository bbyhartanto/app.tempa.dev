<script setup>
import { Head } from '@inertiajs/vue3';
import StoreHomeLayout from '@/Layouts/StoreHomeLayout.vue';
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

    <StoreHomeLayout :tenant="tenant">
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
    </StoreHomeLayout>
</template>
