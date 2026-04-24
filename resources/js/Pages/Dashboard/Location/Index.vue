<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
});

const form = ref({
    address: props.tenant.address || '',
    city: props.tenant.city || '',
    province: props.tenant.province || '',
    google_maps_link: props.tenant.google_maps_link || '',
});

function submit() {
    router.put(route('dashboard.settings.update'), {
        address: form.value.address,
        city: form.value.city,
        province: form.value.province,
        google_maps_link: form.value.google_maps_link,
    }, {
        onSuccess: () => {
            alert('Location saved!');
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Location" />

        <main class="p-4">
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input
                        v-model="form.address"
                        type="text"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="Street address"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input
                            v-model="form.city"
                            type="text"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Province</label>
                        <input
                            v-model="form.province"
                            type="text"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Google Maps Link</label>
                    <input
                        v-model="form.google_maps_link"
                        type="url"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="https://maps.google.com/..."
                    />
                </div>

                <div class="flex space-x-3 pt-2">
                    <a
                        href="/dashboard"
                        class="flex-1 py-3 border border-gray-300 text-gray-700 text-center font-medium rounded-lg"
                    >
                        Back
                    </a>
                    <button
                        type="submit"
                        class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg"
                    >
                        Save
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
