<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    statusOptions: {
        type: Object,
        required: true,
    },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

function applyFilters() {
    router.get('/dashboard/orders', {
        search: search.value,
        status: status.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    status.value = '';
    applyFilters();
}

function goToPage(page) {
    router.get('/dashboard/orders', {
        ...props.filters,
        page: page,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function handleBack() {
    router.visit('/dashboard');
}

const getStatusStyles = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-50 text-yellow-600 border-yellow-100';
        case 'confirmed': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'processing': return 'bg-purple-50 text-purple-600 border-purple-100';
        case 'shipped': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        case 'completed': return 'bg-green-50 text-green-600 border-green-100';
        case 'cancelled': return 'bg-red-50 text-red-600 border-red-100';
        default: return 'bg-gray-50 text-gray-600 border-gray-100';
    }
};
</script>

<template>
    <Head title="Orders" />

    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="handleBack" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h1 class="text-xl font-extrabold tracking-tight">Orders</h1>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[12px] font-black text-gray-300 uppercase tracking-widest">{{ orders.total }} Total</span>
            </div>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-6">
            <!-- Filters Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-5">
                <div class="space-y-4">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-black transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input 
                            v-model="search" 
                            type="text" 
                            @keyup.enter="applyFilters"
                            placeholder="Order # or customer..." 
                            class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-12 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                        />
                    </div>

                    <div class="flex space-x-3">
                        <div class="flex-1 relative">
                            <select
                                v-model="status"
                                @change="applyFilters"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all appearance-none cursor-pointer"
                            >
                                <option value="">All Status</option>
                                <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                                    {{ label }}
                                </option>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                        </div>
                        <button 
                            @click="clearFilters"
                            class="px-5 bg-gray-50 text-gray-400 rounded-[10px] font-bold text-[13px] hover:text-black transition-all active:scale-95"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Orders List -->
            <div v-if="orders.data.length === 0" class="text-center py-20 space-y-6">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto grayscale opacity-40 text-4xl">
                    🧾
                </div>
                <div class="space-y-2">
                    <p class="text-xl font-black text-gray-900">No orders found</p>
                    <p class="text-gray-400 font-bold text-sm">Customer orders will appear here.</p>
                </div>
            </div>

            <div v-else class="space-y-4">
                <Link 
                    v-for="order in orders.data" 
                    :key="order.id" 
                    :href="`/dashboard/orders/${order.id}`"
                    class="block bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.02)] hover:shadow-[0_15px_50px_rgb(0,0,0,0.05)] hover:scale-[1.01] transition-all group"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <span class="text-[16px] font-black tracking-tight text-gray-900">#{{ order.order_number }}</span>
                                <span v-if="order.is_modified" class="bg-orange-50 text-orange-600 text-[10px] font-black uppercase px-2 py-0.5 rounded-lg border border-orange-100">MOD</span>
                            </div>
                            <p class="text-[13px] font-bold text-gray-400">{{ order.created_at_formatted }}</p>
                        </div>
                        <span 
                            class="px-3 py-1 rounded-xl text-[11px] font-black uppercase tracking-wider border transition-colors"
                            :class="getStatusStyles(order.status)"
                        >
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-[10px] bg-gray-50 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform">
                            👤
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-[15px] text-gray-900 truncate leading-snug">{{ order.customer_name }}</p>
                            <p class="text-[13px] font-bold text-gray-400 opacity-60 truncate">{{ order.customer_phone }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[16px] font-black text-gray-900 leading-snug">{{ order.formatted_total }}</p>
                            <p class="text-[12px] font-bold text-gray-300 uppercase tracking-widest">{{ order.active_items_count }} items</p>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="flex items-center justify-center space-x-2 pt-6">
                <button
                    v-for="page in orders.last_page"
                    :key="page"
                    @click="goToPage(page)"
                    :class="page === orders.current_page ? 'bg-black text-white' : 'bg-gray-50 text-gray-400 hover:text-black'"
                    class="w-10 h-10 rounded-xl text-[14px] font-black transition-all active:scale-90"
                >
                    {{ page }}
                </button>
            </div>
        </main>
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
