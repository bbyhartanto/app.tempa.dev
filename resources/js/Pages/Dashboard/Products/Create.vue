<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const form = ref({
    title: '',
    description: '',
    price: '',
    currency: 'IDR',
    images: [],
    is_available: true,
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
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-blue-600 text-white px-4 py-3 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <a href="/dashboard/products" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-lg font-bold">Add Product</h1>
                <div class="w-8"></div>
            </div>
        </header>

        <!-- Form -->
        <main class="p-4">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="bg-white rounded-lg shadow p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Name *</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            required 
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': errors.title }"
                        />
                        <p v-if="errors.title" class="mt-1 text-xs text-red-600">{{ errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3" 
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price *</label>
                            <input 
                                v-model="form.price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Currency</label>
                            <select 
                                v-model="form.currency" 
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="IDR">IDR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Product Images</label>
                        <p class="text-xs text-gray-500 mb-2">Upload pre-compressed images (JPEG, PNG, WebP, max 10MB each)</p>
                        
                        <!-- Image Previews -->
                        <div v-if="imagePreview.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <div v-for="(preview, index) in imagePreview" :key="index" class="relative h-20 w-20 rounded-lg border overflow-hidden">
                                <img :src="preview" class="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    @click="removeImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
                                >×</button>
                            </div>
                        </div>

                        <!-- Uploaded Message -->
                        <p v-if="imagePreview.length > 0" class="mt-2 text-sm text-green-600">Image uploaded. Remove it to replace.</p>

                        <!-- Upload Button -->
                        <div v-if="imagePreview.length === 0" class="mt-2">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Click to upload image</p>
                                    <p class="text-xs text-gray-400">Max 1 image</p>
                                </div>
                                <input
                                    type="file"
                                    @change="handleImageUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                        </div>
                        
                        <p v-if="errors.images" class="mt-1 text-xs text-red-600">{{ errors.images }}</p>
                    </div>

                    <div class="flex items-center">
                        <input 
                            v-model="form.is_available" 
                            type="checkbox" 
                            id="is_available" 
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="is_available" class="ml-2 block text-sm text-gray-700">Available for sale</label>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <a href="/dashboard/products" class="flex-1 py-3 border border-gray-300 text-gray-700 text-center font-medium rounded-lg">
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg"
                    >
                        Create Product
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
