<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import TenantDashboardHeader from '@/Components/navigation/TenantDashboardHeader.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const form = ref({
    title: props.product.title,
    description: props.product.description || '',
    price: props.product.price,
    currency: props.product.currency,
    images: [], // Will hold File objects for new uploads
    is_available: props.product.is_available,
    dine_in_enabled: props.product.dine_in_enabled ?? false,
    sort_order: props.product.sort_order || 0,
});

// Convert storage paths to display URLs
const existingImages = ref((props.product.images || []).map(path => {
    if (path.startsWith('http')) return path;
    // Convert storage path to full URL
    return `${window.location.protocol}//${window.location.host}/storage/${path}`;
}));

// Store the raw paths for removal (not the full URLs)
const removedImages = ref([]); // Storage paths to remove
const imagePreview = ref([]); // Previews for new uploads

const errors = ref({});

function submit() {
    const payload = {
        ...form.value,
        remove_images: removedImages.value,
    };

    // Limit to max 1 image
    if (payload.images && payload.images.length > 1) {
        payload.images = payload.images.slice(0, 1);
    }

    router.put(route('dashboard.products.update', props.product.id), payload, {
        onError: (errs) => {
            errors.value = errs;
        },
    });
}

function handleImageUpload(event) {
    const files = event.target.files;
    if (!files.length) return;

    // Max 1 image per product
    const currentCount = existingImages.value.length + imagePreview.value.length;
    if (currentCount >= 1) {
        errors.value.images = 'Only 1 image is allowed per product. Remove the existing image first.';
        event.target.value = '';
        return;
    }

    const file = files[0]; // Only take first file

    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        errors.value.images = 'Only JPEG, PNG, and WebP images are allowed';
        event.target.value = '';
        return;
    }

    // Validate file size (10MB max)
    if (file.size > 10 * 1024 * 1024) {
        errors.value.images = 'Image must be less than 10MB';
        event.target.value = '';
        return;
    }

    errors.value.images = null;
    form.value.images.push(file);

    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value.push(e.target.result);
    };
    reader.readAsDataURL(file);

    // Clear the input
    event.target.value = '';
}

function removeExistingImage(index) {
    // Get the display URL
    const imageUrl = existingImages.value[index];
    // Extract the storage path from the URL
    const pathMatch = imageUrl.match(/\/storage\/(.+)$/);
    const storagePath = pathMatch ? pathMatch[1] : imageUrl;
    removedImages.value.push(storagePath);
    existingImages.value.splice(index, 1);
}

function removeNewImage(index) {
    form.value.images.splice(index, 1);
    imagePreview.value.splice(index, 1);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <TenantDashboardHeader title="Edit Product" :back-url="route('dashboard.products.index')" />

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
                        
                        <!-- Existing Images -->
                        <div v-if="existingImages.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <div v-for="(img, index) in existingImages" :key="'existing-' + index" class="relative h-20 w-20 rounded-lg border overflow-hidden">
                                <img :src="img" class="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    @click="removeExistingImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
                                >×</button>
                            </div>
                        </div>
                        
                        <!-- New Image Previews -->
                        <div v-if="imagePreview.length > 0" class="mt-2 flex flex-wrap gap-2">
                            <div v-for="(preview, index) in imagePreview" :key="'new-' + index" class="relative h-20 w-20 rounded-lg border overflow-hidden">
                                <img :src="preview" class="h-full w-full object-cover" />
                                <button
                                    type="button"
                                    @click="removeNewImage(index)"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
                                >×</button>
                            </div>
                        </div>
                        
                        <!-- Upload Button -->
                        <div v-if="existingImages.length + imagePreview.length < 1" class="mt-2">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Click to upload</p>
                                    <p class="text-xs text-gray-400">Max 1 image (JPEG, PNG, WebP, 10MB)</p>
                                </div>
                                <input
                                    type="file"
                                    @change="handleImageUpload"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="hidden"
                                />
                            </label>
                        </div>
                        <p v-else class="mt-2 text-xs text-green-600">✓ Image uploaded. Remove it to replace.</p>
                        
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

                    <div class="flex items-center">
                        <input
                            v-model="form.dine_in_enabled"
                            type="checkbox"
                            id="dine_in_enabled"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="dine_in_enabled" class="ml-2 block text-sm text-gray-700">Available for Dine-In Menu</label>
                        <p class="ml-2 text-xs text-gray-500">Show this product in the dine-in menu on storefront</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                        <input 
                            v-model="form.sort_order" 
                            type="number" 
                            min="0" 
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        />
                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
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
                        Update Product
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
