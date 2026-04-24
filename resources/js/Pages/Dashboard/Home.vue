<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';
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
        <TenantDashboardHeader title="Dashboard" :show-logout="true" />

        <!-- Store Info -->
        <div class="bg-white px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <!-- Logo -->
                    <div v-if="tenant.logo_url" class="w-12 h-12 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                        <img :src="tenant.logo_url" :alt="tenant.name" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-12 h-12 rounded-full bg-gray-200 flex-shrink-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-gray-900 truncate">{{ tenant.name }}</h2>
                        <p class="text-sm text-gray-500">/{{ tenant.store_link }}</p>
                        <span
                            :class="{
                                'bg-green-100 text-green-700': tenant.status === 'active',
                                'bg-yellow-100 text-yellow-700': tenant.status === 'pending',
                                'bg-red-100 text-red-700': tenant.status === 'suspended',
                            }"
                            class="inline-block mt-1 px-2 py-1 rounded text-xs font-medium"
                        >
                            {{ tenant.status }}
                        </span>
                    </div>
                </div>
                <a
                    href="/dashboard/settings"
                    class="text-gray-400 hover:text-gray-700 transition p-2"
                    title="Settings"
                >
                    <i class="fa-solid fa-gear text-lg"></i>
                </a>
            </div>
        </div>

        <!-- Subscription Status -->
        <div class="bg-white px-4 py-3">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">Subscription Status</h3>
                <span :class="subscription.badge_class" class="px-2 py-1 rounded text-xs font-medium">
                    {{ subscription.status_label }}
                </span>
            </div>
            
            <!-- Active Subscription Plan Details -->
            <div v-if="tenant.current_plan" class="mt-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg">
                <div class="text-xs font-medium text-green-800 mb-2">✅ Active Plan:</div>
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-base font-bold text-green-900">{{ tenant.current_plan.name }}</div>
                        <div class="text-xs text-green-700 mt-1">📅 {{ tenant.current_plan.billing_cycle }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-green-900">{{ tenant.current_plan.price }}</div>
                        <div class="text-xs text-green-700 mt-1">📦 {{ tenant.current_plan.item_limit }} product slots</div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-t border-green-200 flex items-center justify-between text-xs">
                    <span class="text-green-700">📅 Valid until: {{ tenant.current_plan.end_date }}</span>
                    <span :class="tenant.current_plan.days_remaining <= 7 ? 'text-red-600 font-bold' : 'text-green-800 font-bold'">
                        {{ tenant.current_plan.days_remaining }} days left
                    </span>
                </div>
            </div>
            
            <div v-if="tenant.enabled_modules?.includes('catalog')" class="grid grid-cols-1 gap-3 text-xs mt-3">
                <div>
                    <span class="text-gray-500">Product Usage:</span>
                    <div class="text-sm font-medium mt-1">
                        {{ subscription.remaining_slots }} / {{ tenant.item_limit }} slots remaining
                    </div>
                </div>
            </div>

            <!-- CTA Button -->
            <div v-if="tenant.subscription_status !== 'subscribed'" class="mt-3 pt-3">
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
                <div v-else-if="tenant.subscription_request_status === 'pending'" class="text-center space-y-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        ⏳ Request Pending Admin Approval
                    </span>
                    <!-- Show requested plan details -->
                    <div v-if="tenant.requested_plan" class="mt-2 p-3 bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-lg">
                        <div class="text-sm font-medium text-purple-900 mb-1">Requested Plan:</div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-purple-700">{{ tenant.requested_plan.name }}</span>
                            <span class="font-bold text-purple-900 text-lg">{{ tenant.requested_plan.price }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-purple-600 mt-1">
                            <span>📅 {{ tenant.requested_plan.billing_cycle }}</span>
                            <span>📦 {{ tenant.requested_plan.item_limit }} product slots</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Grid -->
        <main class="p-4">
            <div class="space-y-3">
                <a
                    v-if="tenant.enabled_modules?.includes('catalog')"
                    href="/dashboard/products"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">📦</div>
                    <div class="text-sm font-medium text-gray-700">Products</div>
                </a>

                <a
                    v-if="tenant.enabled_modules?.includes('booking')"
                    href="/dashboard/services"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">📅</div>
                    <div class="text-sm font-medium text-gray-700">Services</div>
                </a>

                <a
                    href="/dashboard/orders"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">📋</div>
                    <div class="text-sm font-medium text-gray-700">Orders</div>
                </a>

                <a
                    href="/dashboard/links"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">🔗</div>
                    <div class="text-sm font-medium text-gray-700">Links</div>
                </a>

                <a
                    href="/dashboard/location"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">📍</div>
                    <div class="text-sm font-medium text-gray-700">Location</div>
                </a>

                <a
                    href="/dashboard/template"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">🎨</div>
                    <div class="text-sm font-medium text-gray-700">Template</div>
                </a>

                <a
                    :href="route('storefront.home', { store_link: tenant.store_link })"
                    target="_blank"
                    class="bg-white p-4 rounded-lg shadow flex items-center space-x-4 hover:shadow-md active:shadow-sm"
                >
                    <div class="text-2xl">🏪</div>
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
