<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    business_type: '',
});

const processing = ref(false);

function submit() {
    if (!form.business_type) return;

    processing.value = true;
    form.post(route('dashboard.onboarding.store'), {
        onSuccess: () => {
            processing.value = false;
        },
        onError: () => {
            processing.value = false;
        },
    });
}
</script>

<template>
    <Head title="Welcome - Choose Your Business Type" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 px-4">
        <div class="max-w-2xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    Welcome to Your Store!
                </h1>
                <p class="text-lg text-gray-600">
                    What's your business type? This helps us set up the right tools for you.
                </p>
            </div>

            <!-- Selection Cards -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Sell Products -->
                <button
                    type="button"
                    @click="form.business_type = 'catalog'"
                    :class="[
                        'w-full p-6 rounded-2xl border-2 transition-all text-left',
                        form.business_type === 'catalog'
                            ? 'border-blue-500 bg-blue-50 shadow-lg'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow'
                    ]"
                >
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 text-4xl">🛍️</div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">
                                I sell products
                            </h3>
                            <p class="text-gray-600">
                                Physical goods, inventory-based — e.g., clothing, food, crafts
                            </p>
                        </div>
                        <div v-if="form.business_type === 'catalog'" class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Offer Services -->
                <button
                    type="button"
                    @click="form.business_type = 'booking'"
                    :class="[
                        'w-full p-6 rounded-2xl border-2 transition-all text-left',
                        form.business_type === 'booking'
                            ? 'border-blue-500 bg-blue-50 shadow-lg'
                            : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow'
                    ]"
                >
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 text-4xl">📅</div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-1">
                                I offer services
                            </h3>
                            <p class="text-gray-600">
                                Appointment-based bookings — e.g., barbershop, spa, consultation
                            </p>
                        </div>
                        <div v-if="form.business_type === 'booking'" class="flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </button>

                <!-- Both (Premium) -->
                <div
                    class="w-full p-6 rounded-2xl border-2 border-gray-200 bg-gradient-to-r from-amber-50 to-orange-50 text-left relative"
                >
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 text-4xl">✨</div>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="text-xl font-bold text-gray-900">
                                    Both — I do both
                                </h3>
                                <span class="px-2 py-0.5 text-xs font-semibold bg-amber-200 text-amber-800 rounded-full">
                                    Premium
                                </span>
                            </div>
                            <p class="text-gray-600">
                                Sell products AND book services — unlock with Premium plan
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="!form.business_type || processing"
                    class="w-full py-4 bg-blue-600 text-white font-bold text-lg rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition mt-6"
                >
                    <span v-if="processing">Setting up your store...</span>
                    <span v-else>Continue</span>
                </button>
            </form>

            <!-- Footer -->
            <p class="text-center text-sm text-gray-500 mt-6">
                You can always change this later in Settings.
            </p>
        </div>
    </div>
</template>
