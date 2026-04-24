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
    settings: props.tenant.settings || {},
    background_image: props.tenant.background_image || '',
    background_file: null,
    remove_background: false,
});

if (!form.value.settings.background_color) {
    form.value.settings.background_color = '#FFC947';
}
if (!form.value.settings.button_radius) form.value.settings.button_radius = 'rounded-full';
if (!form.value.settings.button_bg_color) form.value.settings.button_bg_color = '#FFD76E';
if (!form.value.settings.button_border_color) form.value.settings.button_border_color = '#ffffff';
if (!form.value.settings.button_text_color) form.value.settings.button_text_color = '#000000';
if (form.value.settings.button_glass_effect === undefined) form.value.settings.button_glass_effect = false;

const backgroundPreview = ref(props.tenant.background_image || null);
const backgroundError = ref('');

function handleBackgroundUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    backgroundError.value = '';

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        backgroundError.value = 'Only JPEG, PNG, and WebP images are allowed';
        event.target.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        backgroundError.value = 'Background must be less than 5MB';
        event.target.value = '';
        return;
    }

    form.value.background_file = file;
    form.value.remove_background = false;

    const reader = new FileReader();
    reader.onload = (e) => {
        backgroundPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
}

function removeBackground() {
    form.value.background_file = null;
    form.value.remove_background = true;
    form.value.background_image = '';
    backgroundPreview.value = null;
    backgroundError.value = '';
}

function submit() {
    const payload = { ...form.value };
    if (!payload.background_image) {
        delete payload.background_image;
    }
    
    const options = {
        onSuccess: () => {
            alert('Template updated!');
        },
    };
    
    if (form.value.background_file) {
        options.forceFormData = true;
    }
    
    router.put(route('dashboard.settings.update'), payload, options);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Template" />

        <main class="p-4">
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-4 space-y-4">


                <div class="border rounded-lg p-4 bg-gray-50 space-y-4">
                    <h4 class="font-medium text-gray-900">Background Configuration</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Background Color</label>
                        <div class="flex items-center mt-1">
                            <input
                                type="color"
                                v-model="form.settings.background_color"
                                class="h-10 w-10 border-0 rounded cursor-pointer p-0"
                            />
                            <input
                                type="text"
                                v-model="form.settings.background_color"
                                class="ml-2 px-3 py-2 border border-gray-300 rounded-lg text-sm w-32 uppercase"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Background Image (Optional)</label>

                        <div v-if="backgroundPreview" class="mb-3">
                            <img :src="backgroundPreview" alt="Background preview" class="h-32 w-full object-cover rounded-lg border object-center" />
                            <button
                                type="button"
                                @click="removeBackground"
                                class="mt-2 text-sm text-red-600 hover:text-red-800"
                            >
                                Remove image
                            </button>
                        </div>

                        <div v-if="!backgroundPreview" class="mt-2">
                            <label
                                class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100"
                            >
                                <div class="flex flex-col items-center justify-center pt-5 pb-4">
                                    <svg class="w-6 h-6 mb-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-xs text-gray-500">Click to upload background</p>
                                    <p class="text-xs text-gray-400">PNG, JPG, WebP (max 5MB)</p>
                                </div>
                                <input
                                    type="file"
                                    @change="handleBackgroundUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                        </div>

                        <div v-else class="mt-2">
                            <label
                                class="flex items-center justify-center w-full h-10 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 text-sm text-gray-500"
                            >
                                <span>Change image</span>
                                <input
                                    type="file"
                                    @change="handleBackgroundUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                        </div>

                        <p v-if="backgroundError" class="mt-2 text-xs text-red-600">{{ backgroundError }}</p>
                    </div>
                </div>

                <div class="border rounded-lg p-4 bg-gray-50 space-y-4">
                    <h4 class="font-medium text-gray-900">Button Configuration</h4>
                    
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700">Enable Glassmorphism Effect</label>
                        <input
                            type="checkbox"
                            v-model="form.settings.button_glass_effect"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Shape</label>
                        <select
                            v-model="form.settings.button_radius"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        >
                            <option value="rounded-none">Square</option>
                            <option value="rounded-xl">Semi Round</option>
                            <option value="rounded-full">Full Round</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Background Color</label>
                        <div class="flex items-center mt-1">
                            <input
                                type="color"
                                v-model="form.settings.button_bg_color"
                                class="h-10 w-10 border-0 rounded cursor-pointer p-0"
                            />
                            <input
                                type="text"
                                v-model="form.settings.button_bg_color"
                                placeholder="e.g. #FFD76E or rgba(255,215,110,1)"
                                class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Border Color</label>
                        <div class="flex items-center mt-1">
                            <input
                                type="color"
                                v-model="form.settings.button_border_color"
                                class="h-10 w-10 border-0 rounded cursor-pointer p-0"
                            />
                            <input
                                type="text"
                                v-model="form.settings.button_border_color"
                                placeholder="e.g. #ffffff or rgba(255,255,255,1)"
                                class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Button Font Color</label>
                        <div class="flex items-center mt-1">
                            <input
                                type="color"
                                v-model="form.settings.button_text_color"
                                class="h-10 w-10 border-0 rounded cursor-pointer p-0"
                            />
                            <input
                                type="text"
                                v-model="form.settings.button_text_color"
                                placeholder="e.g. #000000 or rgba(0,0,0,1)"
                                class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                        </div>
                    </div>
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
