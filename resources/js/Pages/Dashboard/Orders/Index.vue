<script setup>
import { ref, watch } from 'vue';
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
</script>

<template>
    <Head title="Orders" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Orders</h1>
                        <p class="text-sm text-gray-500">{{ orders.total }} total orders</p>
                    </div>
                    <Link
                        href="/dashboard"
                        class="text-sm text-blue-600 hover:text-blue-700"
                    >
                        ← Back to Dashboard
                    </Link>
                </div>
            </div>
        </header>

        <!-- Filters -->
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="bg-white rounded-lg shadow-sm p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Order # or customer name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select
                            v-model="status"
                            @change="applyFilters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        >
                            <option value="">All Status</option>
                            <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Clear Button -->
                    <div class="flex items-end">
                        <button
                            @click="clearFilters"
                            class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <main class="max-w-7xl mx-auto px-4 pb-8">
            <div v-if="orders.data.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                <p class="mt-1 text-sm text-gray-500">Orders from customers will appear here.</p>
            </div>

            <div v-else class="bg-white shadow-sm rounded-lg overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ order.order_number }}</div>
                                    <div v-if="order.is_modified" class="text-xs text-orange-600">Modified</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ order.customer_name }}</div>
                                    <div class="text-sm text-gray-500">{{ order.customer_phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ order.created_at_formatted }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                            'bg-blue-100 text-blue-800': order.status === 'confirmed',
                                            'bg-purple-100 text-purple-800': order.status === 'processing',
                                            'bg-indigo-100 text-indigo-800': order.status === 'shipped',
                                            'bg-green-100 text-green-800': order.status === 'completed',
                                            'bg-red-100 text-red-800': order.status === 'cancelled',
                                        }"
                                    >
                                        {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ order.active_items_count }} / {{ order.items_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ order.formatted_total }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <Link
                                        :href="`/dashboard/orders/${order.id}`"
                                        class="text-blue-600 hover:text-blue-900 font-medium"
                                    >
                                        View Details
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-200">
                    <div v-for="order in orders.data" :key="order.id" class="p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ order.order_number }}</span>
                                <span v-if="order.is_modified" class="ml-2 text-xs text-orange-600">Modified</span>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                    'bg-blue-100 text-blue-800': order.status === 'confirmed',
                                    'bg-purple-100 text-purple-800': order.status === 'processing',
                                    'bg-indigo-100 text-indigo-800': order.status === 'shipped',
                                    'bg-green-100 text-green-800': order.status === 'completed',
                                    'bg-red-100 text-red-800': order.status === 'cancelled',
                                }"
                            >
                                {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                            <div>
                                <p class="text-xs text-gray-500">Customer</p>
                                <p class="text-gray-900">{{ order.customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Date</p>
                                <p class="text-gray-900">{{ order.created_at_formatted }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Items</p>
                                <p class="text-gray-900">{{ order.active_items_count }} / {{ order.items_count }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total</p>
                                <p class="font-medium text-gray-900">{{ order.formatted_total }}</p>
                            </div>
                        </div>

                        <Link
                            :href="`/dashboard/orders/${order.id}`"
                            class="block w-full text-center py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                        >
                            View Details
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.last_page > 1" class="bg-white px-4 py-3 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ (orders.current_page - 1) * orders.per_page + 1 }}</span> to
                            <span class="font-medium">{{ Math.min(orders.current_page * orders.per_page, orders.total) }}</span> of
                            <span class="font-medium">{{ orders.total }}</span> orders
                        </p>
                        <div class="flex space-x-2">
                            <button
                                v-for="page in orders.last_page"
                                :key="page"
                                @click="goToPage(page)"
                                :class="page === orders.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-1 rounded-md text-sm font-medium"
                            >
                                {{ page }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
