<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import DineInMenuHeader from '@/Components/Storefront/DineIn/DineInMenuHeader.vue';
import DineInMenuGrid from '@/Components/Storefront/DineIn/DineInMenuGrid.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Object,
        required: true,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

// Pagination state
const allProducts = ref(props.products.data || []);
const currentPage = ref(props.products.current_page || 1);
const lastPage = ref(props.products.last_page || 1);
const hasMore = ref(props.products.current_page < props.products.last_page);
const loading = ref(false);
const storeLink = props.tenant.store_link;
const totalProducts = ref(props.products.total || 0);

// Load more products (infinite scroll)
async function loadMoreProducts() {
    if (loading.value || !hasMore.value) return;

    loading.value = true;
    const nextPage = currentPage.value + 1;

    try {
        const response = await fetch(`/${props.tenant.store_link}/dine-in/products?page=${nextPage}`, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.products && data.products.length > 0) {
            allProducts.value = [...allProducts.value, ...data.products];
            currentPage.value = data.current_page;
            lastPage.value = data.last_page;
            hasMore.value = data.has_more;
        } else {
            hasMore.value = false;
        }
    } catch (error) {
        console.error('Error loading more products:', error);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Head :title="`${tenant.name} - Dine-In Menu`" />

    <div class="min-h-screen bg-white pb-24">
        <!-- Header -->
        <DineInMenuHeader
            :store-name="tenant.name"
            :product-count="totalProducts"
            :store-link="storeLink"
        />

        <!-- Dine-In Menu Grid with Infinite Scroll -->
        <main class="max-w-md mx-auto px-4 py-6">
            <DineInMenuGrid
                :products="allProducts"
                :store-link="storeLink"
                :loading="loading"
                :has-more="hasMore"
                @load-more="loadMoreProducts"
            />
        </main>
    </div>
</template>
