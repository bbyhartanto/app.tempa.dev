<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    backUrl: {
        type: String,
        default: '/dashboard',
    },
    showLogout: {
        type: Boolean,
        default: false,
    },
});

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <header class="bg-white shadow-sm px-4 py-3 sticky top-0 z-10">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Back Button -->
                <a
                    :href="backUrl"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold text-gray-900">{{ title }}</h1>
            </div>
            <div class="flex items-center space-x-2">
                <slot name="actions" />
                <button
                    v-if="showLogout"
                    @click="logout"
                    class="text-gray-500 hover:text-gray-700 text-sm"
                >
                    Logout
                </button>
            </div>
        </div>
    </header>
</template>
