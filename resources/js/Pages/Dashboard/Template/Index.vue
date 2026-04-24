<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    availableTemplates: {
        type: Array,
        required: true,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

const form = ref({
    template_slug: props.tenant.template_slug,
});

function submit() {
    router.put(route('dashboard.settings.update'), {
        template_slug: form.value.template_slug,
    }, {
        onSuccess: () => {
            alert('Template updated!');
        },
    });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Template" />

        <main class="p-4">
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Store Template</label>
                    <select
                        v-model="form.template_slug"
                        class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    >
                        <option v-for="template in availableTemplates" :key="template.slug" :value="template.slug">
                            {{ template.name }}
                        </option>
                    </select>
                </div>

                <div class="border rounded-lg p-4 bg-gray-50">
                    <h4 class="font-medium text-gray-900 mb-2">Current Template Preview</h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p>
                            Primary Color:
                            <span
                                class="inline-block w-4 h-4 rounded border"
                                :style="{ backgroundColor: templateConfig.colors?.primary }"
                            ></span>
                        </p>
                        <p>Products per row: {{ templateConfig.layout?.products_per_row || 2 }}</p>
                        <p>Show prices: {{ templateConfig.layout?.show_prices !== false ? 'Yes' : 'No' }}</p>
                    </div>
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
