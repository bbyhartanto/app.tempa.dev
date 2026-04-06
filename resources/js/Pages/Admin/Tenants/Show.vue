<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.tenants.index')" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">Tenant Details</h1>
                </div>
                <Link 
                    :href="route('admin.tenants.index')" 
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200"
                >
                    Back to List
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Tenant Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Basic Information</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Store Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ tenant.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Store Link</label>
                                <p class="mt-1 text-sm text-gray-900">/{{ tenant.store_link }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ tenant.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Phone</label>
                                <p class="mt-1 text-sm text-gray-900">{{ tenant.phone || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">WhatsApp</label>
                                <p class="mt-1 text-sm text-gray-900">{{ tenant.whatsapp_number || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Template</label>
                                <p class="mt-1 text-sm text-gray-900">{{ tenant.template_slug }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="tenant.description" class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-2">Description</h2>
                        <p class="text-sm text-gray-700">{{ tenant.description }}</p>
                    </div>

                    <!-- Status & Timestamps -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Status & Timeline</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Status</label>
                                <span :class="{
                                    'bg-yellow-100 text-yellow-800': tenant.status === 'pending',
                                    'bg-green-100 text-green-800': tenant.status === 'active',
                                    'bg-red-100 text-red-800': tenant.status === 'suspended',
                                }" class="mt-1 inline-block px-2 py-1 rounded text-xs font-medium">
                                    {{ tenant.status }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ new Date(tenant.created_at).toLocaleDateString() }}</p>
                            </div>
                            <div v-if="tenant.approved_at">
                                <label class="block text-sm font-medium text-gray-500">Approved At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ new Date(tenant.approved_at).toLocaleDateString() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Statistics</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Products</span>
                                <span class="text-lg font-bold text-gray-900">{{ tenant.products_count || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500">Orders</span>
                                <span class="text-lg font-bold text-gray-900">{{ tenant.orders_count || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h2>
                        <div class="space-y-2">
                            <a 
                                :href="route('storefront.home', { store_link: tenant.store_link })" 
                                target="_blank"
                                class="block w-full py-2 px-4 bg-blue-600 text-white text-center rounded-lg text-sm font-medium hover:bg-blue-700"
                            >
                                View Storefront
                            </a>
                            
                            <Link 
                                :href="route('admin.tenants.subscription.show', tenant.id)"
                                class="block w-full py-2 px-4 bg-purple-600 text-white text-center rounded-lg text-sm font-medium hover:bg-purple-700"
                            >
                                Manage Subscription
                            </Link>

                            <template v-if="tenant.status === 'pending'">
                                <button class="w-full py-2 px-4 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                                    Approve Tenant
                                </button>
                            </template>

                            <template v-if="tenant.status === 'active'">
                                <button class="w-full py-2 px-4 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                                    Suspend Tenant
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
