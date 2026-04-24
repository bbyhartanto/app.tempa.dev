<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
});

const dayOptions = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const form = useForm({
    name: props.service.name,
    slug: props.service.slug,
    description: props.service.description || '',
    price: props.service.price,
    currency: props.service.currency,
    duration_min: props.service.duration_min,
    buffer_min: props.service.buffer_min,
    default_start: props.service.default_start,
    default_end: props.service.default_end,
    available_days: props.service.available_days || [],
    is_available: props.service.is_available,
});

function toggleDay(day) {
    const idx = form.available_days.indexOf(day);
    if (idx > -1) {
        form.available_days.splice(idx, 1);
    } else {
        form.available_days.push(day);
    }
}

function submit() {
    form.put(route('dashboard.services.update', props.service.id));
}
</script>

<template>
    <Head title="Edit Service" />

    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Edit Service" :back-url="route('dashboard.services.index')" />

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Form -->
            <form @submit.prevent="submit" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Price -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                        <input
                            v-model="form.price"
                            type="number"
                            required
                            min="0"
                            step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select
                            v-model="form.currency"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="IDR">IDR</option>
                            <option value="USD">USD</option>
                        </select>
                    </div>
                </div>

                <!-- Duration & Buffer -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                        <input
                            v-model="form.duration_min"
                            type="number"
                            required
                            min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buffer (minutes)</label>
                        <input
                            v-model="form.buffer_min"
                            type="number"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <input
                            v-model="form.default_start"
                            type="time"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                        <input
                            v-model="form.default_end"
                            type="time"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Available Days -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Available Days</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="day in dayOptions"
                            :key="day.value"
                            type="button"
                            @click="toggleDay(day.value)"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition',
                                form.available_days.includes(day.value)
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                            ]"
                        >
                            {{ day.label.substring(0, 3) }}
                        </button>
                    </div>
                </div>

                <!-- Availability Toggle -->
                <div class="flex items-center space-x-2">
                    <input
                        v-model="form.is_available"
                        type="checkbox"
                        id="is_available"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                    <label for="is_available" class="text-sm text-gray-700">Available for booking</label>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                    <Link
                        :href="route('dashboard.services.index')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
