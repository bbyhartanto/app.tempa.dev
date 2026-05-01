<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

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

function handleBack() {
    router.visit('/dashboard');
}
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="handleBack" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h1 class="text-xl font-extrabold tracking-tight">Products</h1>
            </div>
            <Link 
                href="/dashboard/products/create"
                class="bg-black text-white px-5 py-2 rounded-xl text-sm font-bold active:scale-95 transition-all shadow-lg shadow-gray-100"
            >
                + Add
            </Link>
        </header>

        <main class="max-w-md mx-auto px-6 py-6 space-y-6">
            <!-- Search Bar -->
            <div class="relative group">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-black transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Search your products..." 
                    class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-12 py-4 rounded-[10px] text-[15px] font-medium outline-none transition-all placeholder:text-gray-400"
                />
            </div>

            <!-- Products List -->
            <div v-if="filteredProducts.length === 0" class="text-center space-y-3 py-12">
                <div class="text-4xl">🔍</div>
                <p class="text-gray-400 font-bold">No products found</p>
                <button v-if="searchQuery" @click="searchQuery = ''" class="text-blue-500 font-bold text-sm">Clear search</button>
            </div>

            <div v-else class="space-y-4">
                <div 
                    v-for="product in filteredProducts" 
                    :key="product.id" 
                    class="bg-white border-2 border-gray-50 rounded-[28px] p-4 flex items-center space-x-4 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:shadow-[0_12px_40px_rgb(0,0,0,0.04)] transition-all group"
                >
                    <!-- Product Image -->
                    <div class="w-20 h-20 bg-gray-50 rounded-[10px] overflow-hidden flex-shrink-0 border border-gray-100 group-hover:scale-105 transition-transform duration-300">
                        <img 
                            v-if="product.first_image" 
                            :src="product.first_image" 
                            :alt="product.title" 
                            class="w-full h-full object-cover" 
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[16px] text-gray-900 truncate leading-snug">{{ product.title }}</h3>
                        <p class="text-[14px] font-bold text-gray-400 mt-0.5">{{ product.formatted_price }}</p>
                        
                        <div class="flex items-center space-x-3 mt-2">
                            <span 
                                :class="product.is_available ? 'bg-green-50 text-green-600 border-green-100' : 'bg-red-50 text-red-600 border-red-100'"
                                class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase border"
                            >
                                {{ product.is_available ? 'Available' : 'Sold Out' }}
                            </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col items-end space-y-2">
                        <button 
                            @click="toggleAvailability(product)"
                            :class="product.is_available ? 'bg-green-500 text-white shadow-green-100' : 'bg-gray-200 text-gray-500 shadow-gray-50'"
                            class="w-10 h-10 rounded-xl flex items-center justify-center transition-all active:scale-90 shadow-lg"
                        >
                            <svg v-if="product.is_available" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                        <Link 
                            :href="`/dashboard/products/${product.id}/edit`" 
                            class="p-2 text-gray-300 hover:text-black transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
