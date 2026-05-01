<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

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

// Default settings if missing
if (!form.value.settings.background_color) form.value.settings.background_color = '#FFC947';
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

function handleBack() {
    router.visit('/dashboard');
}
</script>

<template>
    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="handleBack" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Custom Theme</h1>
            </div>
            <button 
                @click="submit"
                class="bg-black text-white px-6 py-2.5 rounded-xl text-sm font-bold active:scale-95 transition-all shadow-lg shadow-gray-100"
            >
                Save
            </button>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-8">
            <form @submit.prevent="submit" class="space-y-8">
                
                <!-- Background Section -->
                <div class="bg-white border-2 border-gray-50 rounded-[32px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                            🎨
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Background</h2>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Background Color</label>
                            <div class="flex items-center space-x-3">
                                <div class="relative w-14 h-14 rounded-2xl overflow-hidden border-2 border-gray-100 shadow-sm">
                                    <input
                                        type="color"
                                        v-model="form.settings.background_color"
                                        class="absolute inset-0 w-[200%] h-[200%] -top-[50%] -left-[50%] cursor-pointer border-0 p-0"
                                    />
                                </div>
                                <input
                                    type="text"
                                    v-model="form.settings.background_color"
                                    class="flex-1 bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-2xl text-[15px] font-bold outline-none transition-all uppercase placeholder:text-gray-300"
                                />
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Background Image</label>
                            
                            <div v-if="backgroundPreview" class="relative group aspect-[16/9] rounded-[24px] overflow-hidden border-2 border-gray-50 shadow-sm">
                                <img :src="backgroundPreview" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2 backdrop-blur-sm">
                                    <label class="bg-white text-black px-4 py-2 rounded-xl text-xs font-bold cursor-pointer active:scale-95 transition-all">
                                        Change
                                        <input type="file" @change="handleBackgroundUpload" class="hidden" accept="image/*" />
                                    </label>
                                    <button @click="removeBackground" type="button" class="bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-bold active:scale-95 transition-all">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <label v-else class="flex flex-col items-center justify-center w-full aspect-[16/9] border-4 border-gray-50 border-dashed rounded-[32px] cursor-pointer bg-gray-50/50 hover:bg-gray-50 transition-all group">
                                <div class="flex flex-col items-center justify-center space-y-3 p-6 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                                        📸
                                    </div>
                                    <p class="text-[13px] font-bold text-gray-400">Upload optional background</p>
                                </div>
                                <input
                                    type="file"
                                    @change="handleBackgroundUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                            <p v-if="backgroundError" class="text-xs font-bold text-red-500 text-center">{{ backgroundError }}</p>
                        </div>
                    </div>
                </div>

                <!-- Button Section -->
                <div class="bg-white border-2 border-gray-50 rounded-[32px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-xl border border-orange-100/50">
                            🔘
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Buttons</h2>
                    </div>

                    <div class="space-y-6">
                        <!-- Glass Toggle -->
                        <label class="flex items-center justify-between p-5 bg-gray-50 rounded-[24px] cursor-pointer hover:bg-gray-100/50 transition-all group">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-gray-100">
                                    ✨
                                </div>
                                <div>
                                    <p class="text-[15px] font-black text-gray-900">Glassmorphism</p>
                                    <p class="text-[12px] font-bold text-gray-400">Enable frosted glass effect</p>
                                </div>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.settings.button_glass_effect"
                                    type="checkbox"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            </div>
                        </label>

                        <!-- Shape -->
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Button Shape</label>
                            <div class="grid grid-cols-3 gap-2">
                                <button 
                                    v-for="shape in [{val: 'rounded-none', label: 'Square'}, {val: 'rounded-xl', label: 'Round'}, {val: 'rounded-full', label: 'Pill'}]"
                                    :key="shape.val"
                                    type="button"
                                    @click="form.settings.button_radius = shape.val"
                                    :class="form.settings.button_radius === shape.val ? 'bg-black text-white' : 'bg-gray-50 text-gray-400'"
                                    class="py-3 px-2 rounded-xl text-[13px] font-bold transition-all active:scale-95"
                                >
                                    {{ shape.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Colors -->
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-2">
                                <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Background Color</label>
                                <div class="flex items-center space-x-3">
                                    <div class="relative w-12 h-12 rounded-xl overflow-hidden border-2 border-gray-100 shadow-sm">
                                        <input type="color" v-model="form.settings.button_bg_color" class="absolute inset-0 w-[200%] h-[200%] -top-[50%] -left-[50%] cursor-pointer" />
                                    </div>
                                    <input v-model="form.settings.button_bg_color" type="text" class="flex-1 bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-4 py-3 rounded-2xl text-[14px] font-bold outline-none" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Text Color</label>
                                <div class="flex items-center space-x-3">
                                    <div class="relative w-12 h-12 rounded-xl overflow-hidden border-2 border-gray-100 shadow-sm">
                                        <input type="color" v-model="form.settings.button_text_color" class="absolute inset-0 w-[200%] h-[200%] -top-[50%] -left-[50%] cursor-pointer" />
                                    </div>
                                    <input v-model="form.settings.button_text_color" type="text" class="flex-1 bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-4 py-3 rounded-2xl text-[14px] font-bold outline-none" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Border Color</label>
                                <div class="flex items-center space-x-3">
                                    <div class="relative w-12 h-12 rounded-xl overflow-hidden border-2 border-gray-100 shadow-sm">
                                        <input type="color" v-model="form.settings.button_border_color" class="absolute inset-0 w-[200%] h-[200%] -top-[50%] -left-[50%] cursor-pointer" />
                                    </div>
                                    <input v-model="form.settings.button_border_color" type="text" class="flex-1 bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-4 py-3 rounded-2xl text-[14px] font-bold outline-none" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Preview Section -->
                <div class="space-y-4 pt-4">
                    <div class="flex items-center space-x-3 px-2">
                        <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-sm border border-purple-100/50">
                            👀
                        </div>
                        <h2 class="text-[13px] font-black uppercase tracking-[0.2em] text-gray-400">Current Theme Settings</h2>
                    </div>
                    
                    <div class="bg-gray-50/50 border-2 border-gray-100 rounded-[32px] p-6 space-y-3">
                        <div class="flex items-center justify-between text-[14px]">
                            <span class="font-bold text-gray-400">Primary Color</span>
                            <div class="w-6 h-6 rounded-lg border-2 border-white shadow-sm" :style="{ backgroundColor: templateConfig.colors?.primary }"></div>
                        </div>
                        <div class="flex items-center justify-between text-[14px]">
                            <span class="font-bold text-gray-400">Products per row</span>
                            <span class="font-black text-gray-900">{{ templateConfig.layout?.products_per_row || 2 }}</span>
                        </div>
                        <div class="flex items-center justify-between text-[14px]">
                            <span class="font-bold text-gray-400">Show prices</span>
                            <span class="font-black text-gray-900">{{ templateConfig.layout?.show_prices !== false ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="pt-6 flex flex-col items-center space-y-6">
                    <button
                        type="submit"
                        class="w-full py-5 bg-black text-white rounded-[24px] font-black text-[16px] hover:bg-gray-800 transition-all active:scale-95 shadow-xl shadow-gray-100"
                    >
                        Apply Theme
                    </button>
                    <Link href="/dashboard" class="text-[13px] font-black text-gray-300 uppercase tracking-widest hover:text-gray-900 transition-colors">
                        Cancel and Go Back
                    </Link>
                </div>
            </form>
        </main>
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
