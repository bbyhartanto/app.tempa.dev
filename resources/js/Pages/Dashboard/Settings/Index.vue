<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

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
    name: props.tenant.name,
    email: props.tenant.email,
    phone: props.tenant.phone || '',
    whatsapp_number: props.tenant.whatsapp_number || '',
    logo_url: props.tenant.logo_url || '',
    background_image: props.tenant.background_image || '',
    description: props.tenant.description || '',
    address: props.tenant.address || '',
    city: props.tenant.city || '',
    province: props.tenant.province || '',
    streetname: props.tenant.streetname || '',
    google_maps_link: props.tenant.google_maps_link || '',
    latitude: props.tenant.latitude || '',
    longitude: props.tenant.longitude || '',
    template_slug: props.tenant.template_slug,
    settings: props.tenant.settings || {},
    store_links: props.tenant.store_links || [],
});

const errors = ref({});
const activeTab = ref('general');
const newLink = ref({ label: '', url: '' });

function submit() {
    router.put(route('dashboard.settings.update'), form.value, {
        onError: (errs) => {
            errors.value = errs;
        },
        onSuccess: () => {
            alert('Settings saved successfully!');
        },
    });
}

function addLink() {
    if (newLink.value.label && newLink.value.url) {
        form.value.store_links.push({
            ...newLink.value,
            order: form.value.store_links.length + 1,
        });
        newLink.value = { label: '', url: '' };
    }
}

function removeLink(index) {
    form.value.store_links.splice(index, 1);
}

function moveLink(index, direction) {
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    if (newIndex >= 0 && newIndex < form.value.store_links.length) {
        [form.value.store_links[index], form.value.store_links[newIndex]] = 
        [form.value.store_links[newIndex], form.value.store_links[index]];
    }
}

const tabs = [
    { id: 'general', label: 'General' },
    { id: 'contact', label: 'Contact' },
    { id: 'location', label: 'Location' },
    { id: 'template', label: 'Template' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-blue-600 text-white px-4 py-3 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <a href="/dashboard" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold">Settings</h1>
                <button @click="router.post(route('logout'))" class="text-white/80 hover:text-white text-sm">Logout</button>
            </div>
        </header>

        <!-- Tabs -->
        <div class="bg-white border-b">
            <div class="flex overflow-x-auto">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        activeTab === tab.id
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700',
                        'whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm'
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Form -->
        <main class="p-4">
            <form @submit.prevent="submit" class="space-y-4">
                <!-- General Tab -->
                <div v-show="activeTab === 'general'" class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store Name *</label>
                        <input v-model="form.name" type="text" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="form.description" rows="3" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Logo URL</label>
                        <input v-model="form.logo_url" type="url" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="https://..." />
                        <img v-if="form.logo_url" :src="form.logo_url" alt="Logo preview" class="mt-2 h-16 w-16 rounded-full object-cover" />
                    </div>
                </div>

                <!-- Contact Tab -->
                <div v-show="activeTab === 'contact'" class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input v-model="form.email" type="email" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input v-model="form.phone" type="tel" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="+62..." />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp Number *</label>
                        <input v-model="form.whatsapp_number" type="tel" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="+62..." />
                        <p class="mt-1 text-xs text-gray-500">Orders will be sent to this number</p>
                    </div>
                </div>

                <!-- Location Tab -->
                <div v-show="activeTab === 'location'" class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <input v-model="form.address" type="text" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input v-model="form.city" type="text" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Province</label>
                            <input v-model="form.province" type="text" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Google Maps Link</label>
                        <input v-model="form.google_maps_link" type="url" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="https://maps.google.com/..." />
                    </div>
                </div>

                <!-- Template Tab -->
                <div v-show="activeTab === 'template'" class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store Template</label>
                        <select v-model="form.template_slug" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option v-for="template in availableTemplates" :key="template.slug" :value="template.slug">
                                {{ template.name }}
                            </option>
                        </select>
                    </div>

                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-medium text-gray-900 mb-2">Current Template Preview</h4>
                        <div class="text-sm text-gray-600 space-y-1">
                            <p>Primary Color: <span class="inline-block w-4 h-4 rounded border" :style="{ backgroundColor: templateConfig.colors?.primary }"></span></p>
                            <p>Products per row: {{ templateConfig.layout?.products_per_row || 2 }}</p>
                            <p>Show prices: {{ templateConfig.layout?.show_prices !== false ? 'Yes' : 'No' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex space-x-3 pt-4">
                    <a href="/dashboard" class="flex-1 py-3 border border-gray-300 text-gray-700 text-center font-medium rounded-lg">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
