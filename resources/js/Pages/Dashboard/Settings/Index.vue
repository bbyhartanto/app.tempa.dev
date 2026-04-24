<script setup>
import { ref, computed } from 'vue';
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
    logo_file: null,
    remove_logo: false,
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

const logoPreview = ref(props.tenant.logo_url || null);
const logoError = ref('');
const errors = ref({});
const newLink = ref({ label: '', url: '' });

function handleLogoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    logoError.value = '';

    if (!['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'].includes(file.type)) {
        logoError.value = 'Only JPEG, PNG, WebP, and SVG images are allowed';
        event.target.value = '';
        return;
    }

    if (file.size > 2 * 1024 * 1024) {
        logoError.value = 'Logo must be less than 2MB';
        event.target.value = '';
        return;
    }

    form.value.logo_file = file;
    form.value.remove_logo = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function removeLogo() {
    form.value.logo_file = null;
    form.value.remove_logo = true;
    form.value.logo_url = '';
    logoPreview.value = null;
    logoError.value = '';
}

function submit() {
    const payload = { ...form.value };

    // Clean empty strings that fail URL validation
    if (!payload.logo_url || payload.logo_url === '') {
        delete payload.logo_url;
    }
    
    // Also clean other URL fields that might be empty
    if (!payload.background_image || payload.background_image === '') {
        delete payload.background_image;
    }
    
    if (!payload.google_maps_link || payload.google_maps_link === '') {
        delete payload.google_maps_link;
    }

    console.log('Submitting form with payload:', payload);

    const options = {
        onError: (errs) => {
            errors.value = errs;
            console.log('Validation errors received:', errs);
        },
        onSuccess: () => {
            alert('Settings saved successfully!');
        },
        preserveScroll: true,
    };

    if (form.value.logo_file) {
        options.forceFormData = true;
    }

    router.put(route('dashboard.settings.update'), payload, options);
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

// Modules state
const enabledModules = ref(props.tenant.enabled_modules || ['catalog']);
const modulesSaving = ref(false);
const originalModules = ref([...enabledModules.value]);

const modulesChanged = computed(() =>
    JSON.stringify([...enabledModules.value].sort()) !== JSON.stringify([...originalModules.value].sort())
);

const primaryModule = computed({
    get: () => enabledModules.value.includes('booking') ? 'booking' : 'catalog',
    set: (val) => {
        // If it's already the primary module, do nothing
        if ((val === 'booking' && enabledModules.value.includes('booking')) || 
            (val === 'catalog' && enabledModules.value.includes('catalog'))) {
            return;
        }

        const moduleName = val === 'booking' ? 'Booking' : 'Catalog';
        const hiddenItems = val === 'booking' ? 'products' : 'services';
        const warningMessage = `⚠️ Warning: Changing your primary mode to ${moduleName} will hide all ${hiddenItems} from your storefront.\n\nAre you sure you want to continue?`;

        if (!confirm(warningMessage)) {
            return; // User cancelled
        }

        if (val === 'catalog') {
            enabledModules.value = enabledModules.value.filter(m => m !== 'booking');
            if (!enabledModules.value.includes('catalog')) enabledModules.value.push('catalog');
        } else {
            enabledModules.value = enabledModules.value.filter(m => m !== 'catalog' && m !== 'dine_in');
            if (!enabledModules.value.includes('booking')) enabledModules.value.push('booking');
        }
    }
});

function toggleDineIn() {
    const idx = enabledModules.value.indexOf('dine_in');
    if (idx > -1) {
        enabledModules.value.splice(idx, 1);
    } else {
        enabledModules.value.push('dine_in');
    }
}

async function saveModules() {
    // Warn if no modules enabled
    if (enabledModules.value.length === 0) {
        const confirmEmpty = confirm(
            '⚠️ Warning: No modules are enabled.\n\n' +
            'Your storefront will be empty and visitors will not see any products or services.\n\n' +
            'Do you want to continue?'
        );
        if (!confirmEmpty) {
            return;
        }
    }

    modulesSaving.value = true;
    router.post(route('dashboard.settings.modules.update'), {
        modules: enabledModules.value,
    }, {
        onSuccess: () => {
            alert('Modules updated!');
            originalModules.value = [...enabledModules.value];
            modulesSaving.value = false;
        },
        onError: () => {
            modulesSaving.value = false;
        },
        preserveState: true,
    });
}

function requestUpgrade() {
    router.visit('/dashboard#subscription');
}
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

        <!-- Form -->
        <main class="p-4">
            <!-- Error Messages -->
            <div v-if="Object.keys(errors).length > 0" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-sm font-medium text-red-800 mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-sm text-red-700">
                    <li v-for="(error, field) in errors" :key="field">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Store Modules Card -->
                <div class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 mb-1">Store Modules</h3>
                        <p class="text-sm text-gray-500 mb-4">Choose which features to enable for your storefront.</p>

                        <!-- Catalog Module -->
                        <div class="flex items-center justify-between p-4 border rounded-lg mb-3" :class="primaryModule === 'catalog' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">🛍️</span>
                                <div>
                                    <p class="font-medium text-gray-900">Catalog</p>
                                    <p class="text-sm text-gray-500">Sell products with cart & checkout</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="radio"
                                    value="catalog"
                                    v-model="primaryModule"
                                    class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                                />
                            </label>
                        </div>

                        <!-- Dine-In Menu (Sub-feature of Catalog) -->
                        <div v-if="primaryModule === 'catalog'" class="flex items-center justify-between p-4 border border-orange-200 bg-orange-50 rounded-lg mb-3 ml-6">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">🍽️</span>
                                <div>
                                    <p class="font-medium text-gray-900">Dine-In Menu</p>
                                    <p class="text-sm text-gray-500">Show dine-in menu on storefront (display only, no cart)</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="enabledModules.includes('dine_in')"
                                    @change="toggleDineIn"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>

                        <!-- Booking Module -->
                        <div class="flex items-center justify-between p-4 border rounded-lg mb-3" :class="primaryModule === 'booking' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">📅</span>
                                <div>
                                    <p class="font-medium text-gray-900">Booking</p>
                                    <p class="text-sm text-gray-500">Accept service appointments & bookings</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="radio"
                                    value="booking"
                                    v-model="primaryModule"
                                    class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                                />
                            </label>
                        </div>

                        <button
                            type="button"
                            @click="saveModules"
                            :disabled="modulesSaving || !modulesChanged"
                            class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ modulesSaving ? 'Saving...' : 'Save Modules' }}
                        </button>
                    </div>
                </div>

                <!-- General Info Card -->
                <div class="bg-white rounded-lg shadow p-4 space-y-4">
                    <h3 class="text-base font-semibold text-gray-900">General Info</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Store Name *</label>
                        <input v-model="form.name" type="text" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea v-model="form.description" rows="3" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Store Logo</label>

                        <!-- Current Logo Preview -->
                        <div v-if="logoPreview" class="mb-3">
                            <img :src="logoPreview" alt="Current logo" class="h-20 w-20 rounded-full object-cover border" />
                            <button
                                type="button"
                                @click="removeLogo"
                                class="mt-2 text-sm text-red-600 hover:text-red-800"
                            >
                                Remove logo
                            </button>
                        </div>

                        <!-- Upload Area (show only when no logo) -->
                        <div v-if="!logoPreview" class="mt-2">
                            <label
                                class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
                            >
                                <div class="flex flex-col items-center justify-center pt-5 pb-4">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-xs text-gray-500">Click to upload logo</p>
                                    <p class="text-xs text-gray-400">PNG, JPG, WebP, SVG (max 2MB)</p>
                                </div>
                                <input
                                    type="file"
                                    @change="handleLogoUpload"
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    class="hidden"
                                />
                            </label>
                        </div>

                        <!-- Replace Upload Area (show when logo exists) -->
                        <div v-else class="mt-2">
                            <label
                                class="flex items-center justify-center w-full h-10 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 text-sm text-gray-500"
                            >
                                <span>Change logo</span>
                                <input
                                    type="file"
                                    @change="handleLogoUpload"
                                    accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                    class="hidden"
                                />
                            </label>
                        </div>

                        <p v-if="logoError" class="mt-2 text-xs text-red-600">{{ logoError }}</p>
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="bg-white rounded-lg shadow p-4 space-y-4">
                    <h3 class="text-base font-semibold text-gray-900">Contact Information</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input v-model="form.email" type="email" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" />
                        <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input v-model="form.phone" type="tel" class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="+62..." />
                        <p v-if="errors.phone" class="mt-1 text-xs text-red-600">{{ errors.phone }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">WhatsApp Number *</label>
                        <input v-model="form.whatsapp_number" type="tel" required class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="+62..." />
                        <p v-if="errors.whatsapp_number" class="mt-1 text-xs text-red-600">{{ errors.whatsapp_number }}</p>
                        <p v-if="!errors.whatsapp_number" class="mt-1 text-xs text-gray-500">Orders will be sent to this number</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
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
