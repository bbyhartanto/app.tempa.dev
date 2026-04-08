<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    availablePlans: {
        type: Array,
        required: true,
    },
    tenantId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(['close']);

const selectedPlanId = ref(null);
const selectedBillingCycle = ref('3_months');
const isSubmitting = ref(false);

const filteredPlans = computed(() => {
    if (!selectedBillingCycle.value) return props.availablePlans;
    return props.availablePlans.filter(p => p.billing_cycle === selectedBillingCycle.value);
});

const selectedPlan = computed(() => {
    return filteredPlans.value.find(p => p.id === selectedPlanId.value);
});

const endDatePreview = computed(() => {
    if (!selectedPlan.value) return null;
    const start = new Date();
    start.setMonth(start.getMonth() + selectedPlan.value.duration_months);
    return start.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
});

function submitRequest() {
    if (!selectedPlanId.value) {
        alert('Please select a subscription plan');
        return;
    }

    isSubmitting.value = true;

    router.post(
        route('dashboard.subscription.request'),
        {
            plan_id: selectedPlanId.value,
            billing_cycle: selectedBillingCycle.value,
        },
        {
            onSuccess: () => {
                emit('close');
                resetForm();
            },
            onError: () => {
                isSubmitting.value = false;
            },
        }
    );
}

function resetForm() {
    selectedPlanId.value = null;
    selectedBillingCycle.value = '3_months';
}

function closeModal() {
    emit('close');
    resetForm();
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closeModal" />

            <!-- Modal -->
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 z-10">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Upgrade Subscription</h2>
                        <p class="text-sm text-gray-500 mt-1">Choose a plan to activate your subscription</p>
                    </div>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Billing Cycle Toggle -->
                <div class="flex gap-2 mb-6">
                    <button
                        @click="selectedBillingCycle = '3_months'"
                        :class="selectedBillingCycle === '3_months' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="flex-1 py-2 px-4 rounded-lg font-medium transition"
                    >
                        3 Months
                    </button>
                    <button
                        @click="selectedBillingCycle = '1_year'"
                        :class="selectedBillingCycle === '1_year' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                        class="flex-1 py-2 px-4 rounded-lg font-medium transition"
                    >
                        1 Year (Save 17%)
                    </button>
                </div>

                <!-- Plan Selection -->
                <div class="space-y-3 mb-6">
                    <label
                        v-for="plan in filteredPlans"
                        :key="plan.id"
                        :class="[
                            selectedPlanId === plan.id
                                ? 'border-blue-500 bg-blue-50 ring-2 ring-blue-500'
                                : 'border-gray-200 hover:border-gray-300',
                            'border rounded-xl p-4 cursor-pointer transition block'
                        ]"
                    >
                        <input
                            type="radio"
                            v-model="selectedPlanId"
                            :value="plan.id"
                            class="hidden"
                        />
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="font-bold text-gray-900">{{ plan.tier_label }}</div>
                                <div class="text-sm text-gray-600 mt-1">{{ plan.item_limit }} products included</div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900">{{ plan.formatted_price }}</div>
                                <div class="text-xs text-gray-500">{{ plan.billing_cycle_label }}</div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Preview -->
                <div v-if="selectedPlan" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="text-sm text-green-800">
                        <div class="font-medium mb-1">Subscription Preview:</div>
                        <div>✅ {{ selectedPlan.tier_label }}</div>
                        <div>📅 Until: {{ endDatePreview }}</div>
                        <div>📦 {{ selectedPlan.item_limit }} product slots</div>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3 text-sm text-blue-800">
                            <p class="font-medium">How it works:</p>
                            <ol class="list-decimal list-inside mt-1 space-y-1">
                                <li>Select your preferred plan and billing cycle</li>
                                <li>Submit your request</li>
                                <li>Admin will review and activate your subscription</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button
                        @click="closeModal"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitRequest"
                        :disabled="!selectedPlanId || isSubmitting"
                        class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                    >
                        {{ isSubmitting ? 'Submitting...' : 'Request Activation' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
