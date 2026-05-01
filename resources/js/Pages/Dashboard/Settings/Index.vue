<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: {
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

    // Clean empty strings for URL validation
    if (!payload.logo_url || payload.logo_url === '') {
        delete payload.logo_url;
    }
    
    if (!payload.background_image || payload.background_image === '') {
        delete payload.background_image;
    }
    
    if (!payload.google_maps_link || payload.google_maps_link === '') {
        delete payload.google_maps_link;
    }

    const options = {
        onError: (errs) => {
            errors.value = errs;
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

// Modules state
const enabledModules = ref(props.tenant.enabled_modules || ['catalog']);
const modulesSaving = ref(false);

const primaryModule = computed({
    get: () => enabledModules.value.includes('booking') ? 'booking' : 'catalog',
    set: (val) => {
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
    modulesSaving.value = true;
    router.post(route('dashboard.settings.modules.update'), {
        modules: enabledModules.value,
    }, {
        onSuccess: () => {
            alert('Modules updated!');
            modulesSaving.value = false;
        },
        onError: () => {
            modulesSaving.value = false;
        },
        preserveState: true,
    });
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
                <h1 class="text-xl font-extrabold tracking-tight">Settings</h1>
            </div>
            <button 
                @click="submit"
                class="bg-black text-white px-6 py-2.5 rounded-xl text-sm font-bold active:scale-95 transition-all shadow-lg shadow-gray-100"
            >
                Save
            </button>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-10">
            <!-- Profile Section -->
            <div class="flex flex-col items-center space-y-4 pt-4">
                <div class="relative group">
                    <div class="w-32 h-32 rounded-full bg-gray-50 overflow-hidden border-4 border-white shadow-xl ring-2 ring-gray-50">
                        <img 
                            v-if="logoPreview" 
                            :src="logoPreview" 
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                    </div>
                    <label class="absolute bottom-1 right-1 bg-white p-2.5 rounded-full shadow-lg border border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors active:scale-90">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><line x1="16" y1="5" x2="22" y2="5"/><line x1="19" y1="2" x2="19" y2="8"/></svg>
                        <input type="file" @change="handleLogoUpload" class="hidden" accept="image/*" />
                    </label>
                </div>
                <div class="text-center space-y-1">
                    <h2 class="text-2xl font-black tracking-tight text-gray-900">{{ tenant.name }}</h2>
                    <p class="text-[14px] font-bold text-gray-400 uppercase tracking-widest">Store Profile</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- General Info Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                            🏢
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">General Info</h2>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Store Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                                placeholder="Enter store name"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300 resize-none min-h-[100px]"
                                placeholder="Describe your business..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modules Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl border border-purple-100/50">
                            ⚙️
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Store Modules</h2>
                    </div>

                    <div class="space-y-4">
                        <label 
                            class="flex items-center justify-between p-5 bg-gray-50 rounded-[24px] cursor-pointer hover:bg-gray-100/50 transition-all group border-2 border-transparent"
                            :class="primaryModule === 'catalog' ? 'border-gray-100' : ''"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-gray-100">
                                    🛍️
                                </div>
                                <div>
                                    <p class="text-[15px] font-black text-gray-900">Catalog</p>
                                    <p class="text-[12px] font-bold text-gray-400">Products & Checkout</p>
                                </div>
                            </div>
                            <input type="radio" value="catalog" v-model="primaryModule" class="w-6 h-6 text-black focus:ring-0 cursor-pointer" />
                        </label>

                        <label 
                            class="flex items-center justify-between p-5 bg-gray-50 rounded-[24px] cursor-pointer hover:bg-gray-100/50 transition-all group border-2 border-transparent"
                            :class="primaryModule === 'booking' ? 'border-gray-100' : ''"
                        >
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-gray-100">
                                    📅
                                </div>
                                <div>
                                    <p class="text-[15px] font-black text-gray-900">Booking</p>
                                    <p class="text-[12px] font-bold text-gray-400">Service Appointments</p>
                                </div>
                            </div>
                            <input type="radio" value="booking" v-model="primaryModule" class="w-6 h-6 text-black focus:ring-0 cursor-pointer" />
                        </label>

                        <div v-if="primaryModule === 'catalog'" class="pt-2 pl-4">
                            <label class="flex items-center justify-between p-5 bg-orange-50/30 rounded-[24px] cursor-pointer hover:bg-orange-50/50 transition-all group border border-orange-100">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-orange-50">
                                        🍽️
                                    </div>
                                    <div>
                                        <p class="text-[15px] font-black text-orange-900 leading-tight">Dine-In Menu</p>
                                        <p class="text-[11px] font-bold text-orange-400 uppercase tracking-wider mt-0.5">Scan to view</p>
                                    </div>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" :checked="enabledModules.includes('dine_in')" @change="toggleDineIn" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-orange-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-orange-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </div>
                            </label>
                        </div>

                        <button
                            type="button"
                            @click="saveModules"
                            :disabled="modulesSaving"
                            class="w-full py-4 bg-gray-50 text-gray-900 rounded-[20px] font-black text-[14px] hover:bg-gray-100 transition-all active:scale-95 border-2 border-gray-100 shadow-sm"
                        >
                            {{ modulesSaving ? 'Updating...' : 'Apply Module Changes' }}
                        </button>
                    </div>
                </div>

                <!-- Contact Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center text-xl border border-green-100/50">
                            📞
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Contact Details</h2>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">WhatsApp Number *</label>
                            <input
                                v-model="form.whatsapp_number"
                                type="tel"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                                placeholder="+62..."
                            />
                            <p v-if="errors.whatsapp_number" class="text-xs font-bold text-red-500 ml-1 mt-1">{{ errors.whatsapp_number }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                            />
                            <p v-if="errors.email" class="text-xs font-bold text-red-500 ml-1 mt-1">{{ errors.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 flex flex-col items-center space-y-6">
                    <button
                        type="submit"
                        class="w-full py-5 bg-black text-white rounded-[24px] font-black text-[16px] hover:bg-gray-800 transition-all active:scale-95 shadow-xl shadow-gray-100"
                    >
                        Save All Settings
                    </button>
                    <button 
                        type="button"
                        @click="handleBack"
                        class="text-[13px] font-black text-gray-300 uppercase tracking-widest hover:text-gray-900 transition-colors"
                    >
                        Discard and Go Back
                    </button>
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