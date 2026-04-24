<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    services: {
        type: Array,
        required: true,
    },
});

const form = useForm({});

function toggleAvailability(serviceId) {
    form.put(route('dashboard.services.toggle-availability', serviceId), {
        preserveScroll: true,
    });
}

function deleteService(serviceId) {
    if (confirm('Are you sure you want to delete this service?')) {
        form.delete(route('dashboard.services.destroy', serviceId), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="Services" />

    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Services">
            <template #actions>
                <Link
                    :href="route('dashboard.services.create')"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800"
                >
                    + Add
                </Link>
            </template>
        </TenantDashboardHeader>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Empty State -->
            <div v-if="services.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="text-6xl mb-4">📅</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No services yet</h3>
                <p class="text-gray-600 mb-6">Add your first service to start accepting bookings</p>
                <Link
                    :href="route('dashboard.services.create')"
                    class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                >
                    Add Your First Service
                </Link>
            </div>

            <!-- Services List -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ service.name }}</div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs">{{ service.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ service.duration_min }} min
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ service.formatted_price }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button
                                        @click="toggleAvailability(service.id)"
                                        :class="[
                                            'px-3 py-1 text-xs font-semibold rounded-full transition',
                                            service.is_available
                                                ? 'bg-green-100 text-green-800 hover:bg-green-200'
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ service.is_available ? 'Available' : 'Hidden' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <Link
                                        :href="route('dashboard.services.edit', service.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteService(service.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
