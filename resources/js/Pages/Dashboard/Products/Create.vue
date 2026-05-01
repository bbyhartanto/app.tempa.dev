<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    subscription: {
        type: Object,
        required: true,
    },
});

const form = ref({
    title: '',
    description: '',
    price: '',
    currency: 'IDR',
    images: [],
    is_available: true,
    dine_in_enabled: false,
    sort_order: 0,
});

const errors = ref({});
const imagePreview = ref([]);

function submit() {
    router.post(route('dashboard.products.store'), form.value, {
        onError: (errs) => {
            errors.value = errs;
        },
    });
}

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        errors.value.images = 'Only JPEG, PNG, and WebP images are allowed';
        return;
    }

    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        errors.value.images = 'Each image must be less than 10MB';
        return;
    }

    form.value.images = [file];

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = [e.target.result];
    };
    reader.readAsDataURL(file);

    // Clear the input
    event.target.value = '';
}

function removeImage(index) {
    form.value.images.splice(index, 1);
    imagePreview.value.splice(index, 1);
}

function handleBack() {
    router.visit('/dashboard/products');
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
                <h1 class="text-xl font-extrabold tracking-tight text-gray-900">Add Product</h1>
            </div>
            <button 
                @click="submit"
                :disabled="!subscription.can_add_products"
                class="bg-black text-white px-6 py-2.5 rounded-xl text-sm font-bold active:scale-95 transition-all shadow-lg shadow-gray-100 disabled:opacity-50 disabled:bg-gray-400"
            >
                Create
            </button>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-8">
            <!-- Warnings Section -->
            <div v-if="!subscription.can_add_products" class="bg-red-50 border-2 border-red-100 rounded-[24px] p-5 flex items-start space-x-4">
                <div class="text-2xl mt-1">⚠️</div>
                <div class="space-y-1">
                    <h3 class="text-[14px] font-black text-red-700 uppercase tracking-wider">Product Limit Reached</h3>
                    <p class="text-[14px] font-bold text-red-600/80 leading-relaxed">
                        You've used all {{ subscription.item_limit }} slots. Please upgrade your subscription to add more.
                    </p>
                </div>
            </div>

            <div v-else-if="subscription.remaining_slots <= 5" class="bg-orange-50 border-2 border-orange-100 rounded-[24px] p-5 flex items-start space-x-4">
                <div class="text-2xl mt-1">⚡</div>
                <div class="space-y-1">
                    <h3 class="text-[14px] font-black text-orange-700 uppercase tracking-wider">Limited Slots Remaining</h3>
                    <p class="text-[14px] font-bold text-orange-600/80 leading-relaxed">
                        Only {{ subscription.remaining_slots }} of {{ subscription.item_limit }} slots remaining.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Basic Info Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                            📝
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Basic Information</h2>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Product Name *</label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="e.g., Kopi Susu Aren"
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                                :class="{ 'border-red-100 bg-red-50/30': errors.title }"
                            />
                            <p v-if="errors.title" class="text-xs font-bold text-red-500 ml-1 mt-1">{{ errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Tell customers more about this product..."
                                class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300 min-h-[120px] resize-none"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Price *</label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    placeholder="0"
                                    class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all placeholder:text-gray-300"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[12px] font-black text-gray-400 uppercase tracking-widest ml-1">Currency</label>
                                <div class="relative">
                                    <select
                                        v-model="form.currency"
                                        class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-[10px] text-[15px] font-bold outline-none transition-all appearance-none cursor-pointer pr-10"
                                    >
                                        <option value="IDR">IDR</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-xl border border-orange-100/50">
                            🖼️
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Product Image</h2>
                    </div>

                    <div class="space-y-4">
                        <div v-if="imagePreview.length > 0" class="relative group aspect-square rounded-[24px] overflow-hidden border-2 border-gray-50 shadow-sm">
                            <img :src="imagePreview[0]" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                            <button
                                type="button"
                                @click="removeImage(0)"
                                class="absolute top-4 right-4 bg-red-500/90 backdrop-blur-md text-white rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-widest shadow-lg hover:bg-red-600 transition-all active:scale-90"
                            >
                                Remove
                            </button>
                        </div>

                        <div v-else>
                            <label class="flex flex-col items-center justify-center w-full aspect-square border-4 border-gray-50 border-dashed rounded-[12px] cursor-pointer bg-gray-50/50 hover:bg-gray-50 hover:border-gray-100 transition-all group">
                                <div class="flex flex-col items-center justify-center space-y-4 p-8 text-center">
                                    <div class="w-16 h-16 rounded-[10px] bg-white shadow-sm flex items-center justify-center text-3xl group-hover:scale-110 transition-transform duration-300">
                                        📸
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[15px] font-black text-gray-900">Upload Image</p>
                                        <p class="text-[12px] font-bold text-gray-400 leading-tight">JPEG, PNG or WebP<br/>Max 10MB</p>
                                    </div>
                                </div>
                                <input
                                    type="file"
                                    @change="handleImageUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                        </div>
                        <p v-if="errors.images" class="text-xs font-bold text-red-500 text-center mt-2">{{ errors.images }}</p>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl border border-purple-100/50">
                            ⚙️
                        </div>
                        <h2 class="text-lg font-black tracking-tight text-gray-900">Visibility & Status</h2>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-5 bg-gray-50 rounded-[24px] cursor-pointer hover:bg-gray-100/50 transition-all group border-2 border-transparent focus-within:border-gray-100">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-gray-100">
                                    🛒
                                </div>
                                <div>
                                    <p class="text-[15px] font-black text-gray-900">Available for sale</p>
                                    <p class="text-[12px] font-bold text-gray-400">Show in your online store</p>
                                </div>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.is_available"
                                    type="checkbox"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            </div>
                        </label>

                        <label class="flex items-center justify-between p-5 bg-gray-50 rounded-[24px] cursor-pointer hover:bg-gray-100/50 transition-all group border-2 border-transparent focus-within:border-gray-100">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shadow-sm text-xl border border-gray-100">
                                    🍽️
                                </div>
                                <div>
                                    <p class="text-[15px] font-black text-gray-900">Dine-In Menu</p>
                                    <p class="text-[12px] font-bold text-gray-400">Show in scan-to-order</p>
                                </div>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input
                                    v-model="form.dine_in_enabled"
                                    type="checkbox"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-black"></div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Submit Button Area -->
                <div class="pt-6 flex flex-col items-center space-y-6">
                    <button
                        type="submit"
                        :disabled="!subscription.can_add_products"
                        class="w-full py-5 bg-black text-white rounded-[24px] font-black text-[16px] hover:bg-gray-800 transition-all active:scale-95 shadow-xl shadow-gray-100 disabled:bg-gray-200 disabled:shadow-none disabled:cursor-not-allowed"
                    >
                        {{ subscription.can_add_products ? 'Create Product' : 'Product Limit Reached' }}
                    </button>
                    <Link href="/dashboard/products" class="text-[13px] font-black text-gray-300 uppercase tracking-widest hover:text-gray-900 transition-colors">
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

/* Custom switch styling */
input:checked ~ .peer-checked\:bg-black {
    background-color: black;
}
</style>
