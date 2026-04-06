<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import SubscriptionModal from '@/Components/Admin/SubscriptionModal.vue';

const props = defineProps({
    tenants: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const statusFilter = ref(props.filters.status || 'all');
const showSubscriptionModal = ref(false);
const selectedTenant = ref(null);

function filterByStatus(status) {
    router.get(route('admin.tenants.index'), { status }, {
        preserveState: true,
    });
}

function approveTenant(tenant) {
    if (confirm(`Approve tenant "${tenant.name}"?`)) {
        router.post(route('admin.tenants.approve', tenant.id));
    }
}

function suspendTenant(tenant) {
    if (confirm(`Suspend tenant "${tenant.name}"?`)) {
        router.post(route('admin.tenants.suspend', tenant.id));
    }
}

function reactivateTenant(tenant) {
    if (confirm(`Reactivate tenant "${tenant.name}"?`)) {
        router.post(route('admin.tenants.reactivate', tenant.id));
    }
}

function deleteTenant(tenant) {
    if (confirm(`Delete tenant "${tenant.name}"? This will delete all their data.`)) {
        router.delete(route('admin.tenants.destroy', tenant.id));
    }
}

function openSubscriptionModal(tenant) {
    selectedTenant.value = tenant;
    showSubscriptionModal.value = true;
}

function getSubscriptionBadgeClass(tenant) {
    if (!tenant.subscription_status || tenant.subscription_status === 'trial') {
        return 'bg-blue-100 text-blue-800';
    }
    
    switch (tenant.subscription_status) {
        case 'subscribed':
            return 'bg-green-100 text-green-800';
        case 'grace_period':
            return 'bg-orange-100 text-orange-800';
        case 'expired':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function getSubscriptionLabel(tenant) {
    if (!tenant.subscription_status || tenant.subscription_status === 'trial') {
        return 'Trial';
    }
    
    const labels = {
        subscribed: 'Subscribed',
        grace_period: 'Grace Period',
        expired: 'Expired',
    };
    
    return labels[tenant.subscription_status] || tenant.subscription_status;
}

function getDaysRemaining(tenant) {
    if (tenant.subscription_status === 'trial' && tenant.trial_ends_at) {
        const endDate = new Date(tenant.trial_ends_at);
        const now = new Date();
        const diffTime = endDate - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    }
    
    if (tenant.current_subscription) {
        return tenant.current_subscription.days_remaining;
    }
    
    return null;
}
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <h1 class="text-xl font-bold text-gray-900">Tenant Management</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6">
            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium text-gray-700">Filter:</span>
                    <button
                        @click="filterByStatus('all')"
                        :class="statusFilter === 'all' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        All
                    </button>
                    <button
                        @click="filterByStatus('pending')"
                        :class="statusFilter === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        Pending
                    </button>
                    <button
                        @click="filterByStatus('active')"
                        :class="statusFilter === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        Active
                    </button>
                    <button
                        @click="filterByStatus('suspended')"
                        :class="statusFilter === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'"
                        class="px-3 py-1 rounded-full text-sm font-medium"
                    >
                        Suspended
                    </button>
                </div>
            </div>

            <!-- Tenants Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscription</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="tenant in tenants.data" :key="tenant.id">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ tenant.name }}</div>
                                    <div class="text-sm text-gray-500">/{{ tenant.store_link }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ tenant.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <span :class="getSubscriptionBadgeClass(tenant)" class="px-2 py-1 rounded-full text-xs font-medium inline-block w-fit">
                                        {{ getSubscriptionLabel(tenant) }}
                                    </span>
                                    <div v-if="getDaysRemaining(tenant) !== null" class="text-xs text-gray-500">
                                        <span v-if="getDaysRemaining(tenant) > 0">
                                            {{ getDaysRemaining(tenant) }} days left
                                        </span>
                                        <span v-else class="text-red-600">
                                            Expired
                                        </span>
                                    </div>
                                    <div v-if="tenant.current_subscription" class="text-xs text-gray-500">
                                        {{ tenant.current_subscription.plan.tier_label }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-500">{{ tenant.template_slug }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'bg-yellow-100 text-yellow-800': tenant.status === 'pending',
                                    'bg-green-100 text-green-800': tenant.status === 'active',
                                    'bg-red-100 text-red-800': tenant.status === 'suspended',
                                }" class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ tenant.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ new Date(tenant.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <button 
                                    @click="openSubscriptionModal(tenant)" 
                                    class="text-purple-600 hover:text-purple-900 mr-2"
                                >
                                    Subscription
                                </button>

                                <Link :href="route('admin.tenants.show', tenant.id)" class="text-blue-600 hover:text-blue-900 mr-2">
                                    View
                                </Link>

                                <template v-if="tenant.status === 'pending'">
                                    <button @click="approveTenant(tenant)" class="text-green-600 hover:text-green-900 mr-2">
                                        Approve
                                    </button>
                                </template>

                                <template v-else-if="tenant.status === 'active'">
                                    <button @click="suspendTenant(tenant)" class="text-red-600 hover:text-red-900 mr-2">
                                        Suspend
                                    </button>
                                </template>

                                <template v-else-if="tenant.status === 'suspended'">
                                    <button @click="reactivateTenant(tenant)" class="text-green-600 hover:text-green-900 mr-2">
                                        Reactivate
                                    </button>
                                </template>

                                <button @click="deleteTenant(tenant)" class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="tenants.last_page > 1" class="mt-6 bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-for="page in Math.min(5, tenants.last_page)"
                            :key="page"
                            :href="route('admin.tenants.index', { status: statusFilter, page })"
                            :class="page === tenants.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            class="px-3 py-1 rounded text-sm font-medium"
                        >
                            {{ page }}
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <!-- Subscription Modal -->
        <SubscriptionModal 
            :show="showSubscriptionModal" 
            :tenant="selectedTenant"
            @close="showSubscriptionModal = false" 
        />
    </div>
</template>
