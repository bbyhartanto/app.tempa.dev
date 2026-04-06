<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    tenant: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const selectedPlanId = ref(null);
const startDate = ref(new Date().toISOString().split('T')[0]);
const isLoading = ref(false);

const plans = ref([
    {
        id: 1,
        tier: 'basic',
        tier_label: 'Basic (0-25 items)',
        billing_cycle: '3_months',
        billing_cycle_label: '3 Bulan',
        price: 50000,
        formatted_price: 'Rp 50.000',
        item_limit: 25,
        duration_months: 3,
    },
    {
        id: 2,
        tier: 'basic',
        tier_label: 'Basic (0-25 items)',
        billing_cycle: '1_year',
        billing_cycle_label: '1 Tahun',
        price: 150000,
        formatted_price: 'Rp 150.000',
        item_limit: 25,
        duration_months: 12,
    },
    {
        id: 3,
        tier: 'standard',
        tier_label: 'Standard (0-60 items)',
        billing_cycle: '3_months',
        billing_cycle_label: '3 Bulan',
        price: 100000,
        formatted_price: 'Rp 100.000',
        item_limit: 60,
        duration_months: 3,
    },
    {
        id: 4,
        tier: 'standard',
        tier_label: 'Standard (0-60 items)',
        billing_cycle: '1_year',
        billing_cycle_label: '1 Tahun',
        price: 350000,
        formatted_price: 'Rp 350.000',
        item_limit: 60,
        duration_months: 12,
    },
]);

const selectedPlan = computed(() => {
    return plans.value.find(p => p.id === selectedPlanId.value);
});

const endDate = computed(() => {
    if (!selectedPlan.value || !startDate.value) return null;
    
    const start = new Date(startDate.value);
    start.setMonth(start.getMonth() + selectedPlan.value.duration_months);
    return start.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
});

function closeModal() {
    emit('close');
    resetForm();
}

function resetForm() {
    selectedPlanId.value = null;
    startDate.value = new Date().toISOString().split('T')[0];
}

function activateSubscription() {
    if (!selectedPlanId.value) {
        alert('Please select a plan');
        return;
    }

    isLoading.value = true;

    router.post(
        route('admin.tenants.subscription.activate', props.tenant.id),
        {
            plan_id: selectedPlanId.value,
            start_date: startDate.value,
        },
        {
            onSuccess: () => {
                closeModal();
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
}

function extendTrial() {
    const days = prompt('How many days to extend? (1-30)');
    if (days && days >= 1 && days <= 30) {
        router.post(
            route('admin.tenants.subscription.extend-trial', props.tenant.id),
            { days: parseInt(days) },
            {
                onSuccess: () => {
                    closeModal();
                },
            }
        );
    }
}

function cancelSubscription() {
    if (confirm('Are you sure you want to cancel this subscription?')) {
        router.post(
            route('admin.tenants.subscription.cancel', props.tenant.id),
            {},
            {
                onSuccess: () => {
                    closeModal();
                },
            }
        );
    }
}

watch(() => props.show, (newVal) => {
    if (newVal && props.tenant) {
        resetForm();
    }
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal" />

            <!-- Modal -->
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 z-10">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Manage Subscription</h2>
                        <p class="text-sm text-gray-500 mt-1">{{ tenant?.name }}</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Current Status -->
                <div v-if="tenant" class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="font-medium text-gray-900 mb-2">Current Status</h3>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Subscription:</span>
                            <span class="ml-2 font-medium">{{ tenant.subscription_status || 'Trial' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Item Limit:</span>
                            <span class="ml-2 font-medium">{{ tenant.item_limit || 25 }} items</span>
                        </div>
                        <div v-if="tenant.current_subscription">
                            <span class="text-gray-500">End Date:</span>
                            <span class="ml-2 font-medium">{{ tenant.current_subscription.end_date }}</span>
                        </div>
                        <div v-if="tenant.current_subscription">
                            <span class="text-gray-500">Days Remaining:</span>
                            <span class="ml-2 font-medium">{{ tenant.current_subscription.days_remaining }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-4">
                    <!-- Activate/Renew Subscription -->
                    <div class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Activate / Renew Subscription</h3>
                        
                        <!-- Plan Selection -->
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <label
                                v-for="plan in plans"
                                :key="plan.id"
                                :class="[
                                    selectedPlanId === plan.id
                                        ? 'border-purple-500 bg-purple-50'
                                        : 'border-gray-300 hover:border-gray-400',
                                    'border rounded-lg p-3 cursor-pointer transition'
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="selectedPlanId"
                                    :value="plan.id"
                                    class="hidden"
                                />
                                <div class="font-medium text-sm">{{ plan.tier_label }}</div>
                                <div class="text-xs text-gray-500">{{ plan.billing_cycle_label }}</div>
                                <div class="text-lg font-bold text-gray-900 mt-2">{{ plan.formatted_price }}</div>
                            </label>
                        </div>

                        <!-- Start Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input
                                v-model="startDate"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                        </div>

                        <!-- End Date Preview -->
                        <div v-if="endDate" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                            <div class="text-sm text-blue-800">
                                <span class="font-medium">Subscription End Date:</span> {{ endDate }}
                            </div>
                        </div>

                        <!-- Activate Button -->
                        <button
                            @click="activateSubscription"
                            :disabled="!selectedPlanId || isLoading"
                            class="w-full py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        >
                            {{ isLoading ? 'Activating...' : 'Activate Subscription' }}
                        </button>
                    </div>

                    <!-- Trial Extension -->
                    <div v-if="tenant?.subscription_status === 'trial'" class="border rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-3">Extend Trial Period</h3>
                        <button
                            @click="extendTrial"
                            class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
                        >
                            Extend Trial
                        </button>
                    </div>

                    <!-- Cancel Subscription -->
                    <div v-if="tenant?.subscription_status === 'subscribed'" class="border border-red-200 rounded-lg p-4">
                        <h3 class="font-medium text-red-900 mb-3">Cancel Subscription</h3>
                        <button
                            @click="cancelSubscription"
                            class="w-full py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700"
                        >
                            Cancel Subscription
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 pt-4 border-t">
                    <button
                        @click="closeModal"
                        class="w-full py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
