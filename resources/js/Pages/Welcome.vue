<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const appName = computed(() => page.props.app_name);
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);

// Redirect authenticated users to their dashboard
if (user.value) {
    if (user.value.role === 'super_admin') {
        router.visit('/admin/tenants');
    } else if (user.value.role === 'tenant_owner') {
        router.visit('/dashboard');
    }
}

const demoStores = [
    { name: 'Kopi Senja', link: 'kopi-senja', description: 'Coffee shop' },
    { name: 'BajuKita', link: 'bajukita', description: 'Fashion boutique' },
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-gray-900">{{ appName }}</h1>
                <div class="flex items-center space-x-4">
                    <template v-if="user">
                        <span class="text-sm text-gray-600">Hi, {{ user.name }}</span>
                        <Link
                            :href="user.role === 'super_admin' ? '/admin/tenants' : '/dashboard'"
                            class="text-gray-600 hover:text-gray-900 font-medium"
                        >
                            Dashboard
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-red-600 hover:text-red-800 font-medium"
                        >
                            Logout
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-gray-600 hover:text-gray-900 font-medium">
                            Login
                        </Link>
                        <Link :href="route('register')" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700">
                            Start Free Trial
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <main class="max-w-7xl mx-auto px-4 py-16">
            <div class="text-center mb-16">
                <h2 class="text-5xl font-bold text-gray-900 mb-4">
                    Sell via WhatsApp
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Create your online catalog in minutes. Customers browse, add to cart, and order directly through WhatsApp.
                </p>
            </div>

            <!-- Features -->
            <div class="grid md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Quick Setup</h3>
                    <p class="text-gray-600">Register and have your store ready in under 5 minutes. No technical skills needed.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">WhatsApp Orders</h3>
                    <p class="text-gray-600">Orders are sent as formatted WhatsApp messages. Close sales faster with direct chat.</p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Mobile First</h3>
                    <p class="text-gray-600">Optimized for mobile shopping. Your customers get a smooth experience on any device.</p>
                </div>
            </div>

            <!-- Demo Stores -->
            <div class="mb-16">
                <h3 class="text-2xl font-bold text-gray-900 text-center mb-8">
                    Try Demo Stores
                </h3>
                <div class="grid md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                    <Link
                        v-for="store in demoStores"
                        :key="store.link"
                        :href="route('storefront.home', { store_link: store.link })"
                        class="bg-white p-6 rounded-lg shadow hover:shadow-md transition border border-gray-200"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ store.name }}</h4>
                                <p class="text-sm text-gray-500">{{ store.description }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- CTA -->
            <div v-if="!user" class="text-center">
                <Link
                    :href="route('register')"
                    class="inline-block bg-blue-600 text-white px-8 py-4 rounded-lg font-bold text-lg hover:bg-blue-700 shadow-lg"
                >
                    Start Your Free Store →
                </Link>
                <p class="mt-4 text-sm text-gray-500">
                    No credit card required. Store pending approval.
                </p>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t mt-16">
            <div class="max-w-7xl mx-auto px-4 py-8 text-center text-sm text-gray-500">
                <p>E-Catalog SaaS - Multi-tenant WhatsApp Commerce Platform</p>
            </div>
        </footer>
    </div>
</template>
