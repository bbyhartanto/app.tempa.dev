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
                <a href="/dashboard" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold">Settings</h1>
                <button @click="logout" class="text-white/80 hover:text-white text-sm">Logout</button>
            </div>
        </header>

        <!-- Content -->
        <main class="p-4">
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="font-bold text-gray-900 mb-4">Store Settings</h2>
                <p class="text-sm text-gray-500 mb-4">Configure your store settings here.</p>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store Name</label>
                        <p class="text-gray-900">{{ tenant.name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store Link</label>
                        <p class="text-gray-900">/{{ tenant.store_link }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <span :class="{
                            'bg-green-100 text-green-700': tenant.status === 'active',
                            'bg-yellow-100 text-yellow-700': tenant.status === 'pending',
                            'bg-red-100 text-red-700': tenant.status === 'suspended',
                        }" class="inline-block px-2 py-1 rounded text-xs font-medium">
                            {{ tenant.status }}
                        </span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
