<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import SubscriptionRequestModal from '@/Components/Tenant/SubscriptionRequestModal.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    subscription: {
        type: Object,
        required: true,
    },
    availablePlans: {
        type: Array,
        required: true,
    },
});

const showRequestModal = ref(false);

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

        <!-- Subscription Status -->
        <div class="bg-white px-4 py-3 border-b">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Subscription Status</h3>
                <span :class="subscription.badge_class" class="px-2 py-1 rounded text-xs font-medium">
                    {{ subscription.status_label }}
                </span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-gray-500">Product Usage:</span>
                    <div class="text-sm font-medium mt-1">
                        {{ subscription.remaining_slots }} / {{ tenant.item_limit }} slots remaining
                    </div>
                </div>
                <div v-if="subscription.summary.current_subscription">
                    <span class="text-gray-500">Ends:</span>
                    <div class="text-sm font-medium mt-1">
                        {{ subscription.summary.current_subscription.end_date }}
                    </div>
                </div>
                <div v-if="subscription.summary.trial_days_remaining !== null">
                    <span class="text-gray-500">Trial Ends:</span>
                    <div class="text-sm font-medium mt-1">
                        {{ subscription.summary.trial_days_remaining }} days remaining
                    </div>
                </div>
            </div>
            
            <!-- CTA Button -->
            <div v-if="tenant.subscription_status !== 'subscribed'" class="mt-3 pt-3 border-t">
                <!-- Show request button only if no pending request exists -->
                <div v-if="tenant.subscription_request_status === 'none' || tenant.subscription_request_status === 'rejected'">
                    <button 
                        @click="showRequestModal = true"
                        class="w-full py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-sm"
                    >
                        <template v-if="tenant.subscription_status === 'trial'">
                            🚀 Activate Subscription
                        </template>
                        <template v-else-if="tenant.subscription_status === 'expired'">
                            🔄 Renew Subscription
                        </template>
                        <template v-else>
                            ⬆️ Upgrade Subscription
                        </template>
                    </button>
                </div>
                <!-- Show pending message if request is pending -->
                <div v-else-if="tenant.subscription_request_status === 'pending'" class="text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ⏳ Request Pending Admin Approval
                    </span>
                </div>
            </div>
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

        <!-- Subscription Request Modal -->
        <SubscriptionRequestModal 
            :show="showRequestModal" 
            :tenant-id="tenant.id"
            :available-plans="availablePlans"
            @close="showRequestModal = false" 
        />
    </div>
</template>
