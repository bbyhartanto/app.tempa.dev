<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
});

const searchQuery = ref('');

const filteredProducts = computed(() => {
    return props.products.filter(p => 
        p.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

function toggleAvailability(product) {
    router.put(route('dashboard.products.update', product.id), {
        is_available: !product.is_available,
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Products" :back-url="route('dashboard.home')">
            <template #actions>
                <a
                    href="/dashboard/products/create"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                    + Add
                </a>
            </template>
        </TenantDashboardHeader>

        <!-- Search -->
        <div class="p-4 bg-white border-b">
            <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Search products..." 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>

        <!-- Products List -->
        <main class="p-4">
            <div v-if="filteredProducts.length === 0" class="text-center text-gray-500 py-8">
                No products found
            </div>

            <div v-else class="space-y-3">
                <div 
                    v-for="product in filteredProducts" 
                    :key="product.id" 
                    class="bg-white rounded-lg shadow p-3 flex items-center space-x-3"
                >
                    <div class="w-16 h-16 bg-gray-100 rounded flex-shrink-0 flex items-center justify-center">
                        <img v-if="product.first_image" :src="product.first_image" :alt="product.title" class="w-full h-full object-cover rounded" />
                        <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-gray-900 text-sm truncate">{{ product.title }}</h3>
                        <p class="text-sm text-gray-600">{{ product.formatted_price }}</p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button 
                            @click="toggleAvailability(product)"
                            :class="product.is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                            class="px-2 py-1 rounded text-xs font-medium"
                        >
                            {{ product.is_available ? '✓' : '✗' }}
                        </button>
                        <a :href="`/dashboard/products/${product.id}/edit`" class="text-blue-600 text-sm">Edit</a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
