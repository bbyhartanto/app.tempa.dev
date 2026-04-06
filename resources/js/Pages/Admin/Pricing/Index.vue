<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    plans: {
        type: Array,
        required: true,
    },
});

const editingPlan = ref(null);
const form = ref({
    price: null,
    item_limit: null,
    is_active: true,
});

function startEdit(plan) {
    editingPlan.value = plan.id;
    form.value = {
        price: plan.price,
        item_limit: plan.item_limit,
        is_active: plan.is_active,
    };
}

function cancelEdit() {
    editingPlan.value = null;
    form.value = {
        price: null,
        item_limit: null,
        is_active: true,
    };
}

function savePlan(planId) {
    router.put(
        route('admin.pricing.update', planId),
        form.value,
        {
            onSuccess: () => {
                cancelEdit();
            },
        }
    );
}

function formatPrice(price) {
    return 'Rp ' + Number(price).toLocaleString('id-ID');
}
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
                    <h1 class="text-xl font-bold text-gray-900">Subscription Pricing Configuration</h1>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6">
            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">Pricing Configuration</h3>
                        <div class="mt-2 text-sm text-blue-800">
                            <p>Configure pricing for each subscription tier and billing cycle. Changes take effect immediately for new subscriptions.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billing Cycle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Limit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="plan in plans" :key="plan.id">
                            <!-- Tier -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ plan.tier_label }}</div>
                            </td>

                            <!-- Billing Cycle -->
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ plan.billing_cycle_label }}</div>
                            </td>

                            <!-- Item Limit -->
                            <td class="px-6 py-4">
                                <template v-if="editingPlan === plan.id">
                                    <input
                                        v-model="form.item_limit"
                                        type="number"
                                        min="1"
                                        class="w-24 px-3 py-1 border border-gray-300 rounded text-sm"
                                    />
                                </template>
                                <template v-else>
                                    <div class="text-sm text-gray-900">{{ plan.item_limit }} items</div>
                                </template>
                            </td>

                            <!-- Price -->
                            <td class="px-6 py-4">
                                <template v-if="editingPlan === plan.id">
                                    <input
                                        v-model="form.price"
                                        type="number"
                                        min="0"
                                        step="1000"
                                        class="w-32 px-3 py-1 border border-gray-300 rounded text-sm"
                                    />
                                </template>
                                <template v-else>
                                    <div class="text-sm font-medium text-gray-900">{{ formatPrice(plan.price) }}</div>
                                </template>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <template v-if="editingPlan === plan.id">
                                    <label class="flex items-center">
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="rounded border-gray-300"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </template>
                                <template v-else>
                                    <span :class="plan.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                                        {{ plan.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </template>
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-sm space-x-2">
                                <template v-if="editingPlan === plan.id">
                                    <button
                                        @click="savePlan(plan.id)"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        Save
                                    </button>
                                    <button
                                        @click="cancelEdit"
                                        class="text-gray-600 hover:text-gray-900"
                                    >
                                        Cancel
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        @click="startEdit(plan)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pricing Summary -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Basic Tier Pricing</h3>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">3 Months:</span>
                            <span class="font-medium">{{ formatPrice(plans.find(p => p.tier === 'basic' && p.billing_cycle === '3_months')?.price || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">1 Year:</span>
                            <span class="font-medium">{{ formatPrice(plans.find(p => p.tier === 'basic' && p.billing_cycle === '1_year')?.price || 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Standard Tier Pricing</h3>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">3 Months:</span>
                            <span class="font-medium">{{ formatPrice(plans.find(p => p.tier === 'standard' && p.billing_cycle === '3_months')?.price || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">1 Year:</span>
                            <span class="font-medium">{{ formatPrice(plans.find(p => p.tier === 'standard' && p.billing_cycle === '1_year')?.price || 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
