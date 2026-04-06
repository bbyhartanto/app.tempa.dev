<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
});

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-blue-600 text-white px-4 py-3 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <h1 class="text-lg font-bold">Dashboard</h1>
                <button
                    @click="logout"
                    class="text-white/80 hover:text-white text-sm"
                >
                    Logout
                </button>
            </div>
        </header>

        <!-- Store Info -->
        <div class="bg-white px-4 py-3 border-b">
            <h2 class="font-bold text-gray-900">{{ tenant.name }}</h2>
            <p class="text-sm text-gray-500">/{{ tenant.store_link }}</p>
            <span
                :class="{
                    'bg-green-100 text-green-700': tenant.status === 'active',
                    'bg-yellow-100 text-yellow-700': tenant.status === 'pending',
                    'bg-red-100 text-red-700': tenant.status === 'suspended',
                }"
                class="inline-block mt-2 px-2 py-1 rounded text-xs font-medium"
            >
                {{ tenant.status }}
            </span>
        </div>

        <!-- Menu Grid -->
        <main class="p-4">
            <div class="grid grid-cols-2 gap-3">
                <a
                    href="/dashboard/products"
                    class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl mb-2">📦</div>
                    <div class="text-sm font-medium text-gray-700">Products</div>
                </a>

                <a
                    href="/dashboard/orders"
                    class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl mb-2">📋</div>
                    <div class="text-sm font-medium text-gray-700">Orders</div>
                </a>

                <a
                    href="/dashboard/links"
                    class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl mb-2">🔗</div>
                    <div class="text-sm font-medium text-gray-700">Links</div>
                </a>

                <a
                    href="/dashboard/settings"
                    class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl mb-2">⚙️</div>
                    <div class="text-sm font-medium text-gray-700">Settings</div>
                </a>

                <a
                    :href="route('storefront.home', { store_link: tenant.store_link })"
                    target="_blank"
                    class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl mb-2">🏪</div>
                    <div class="text-sm font-medium text-gray-700">View Store</div>
                </a>
            </div>
        </main>
    </div>
</template>
