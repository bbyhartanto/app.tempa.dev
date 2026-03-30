<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

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
    images: props.product.images || [],
    is_available: props.product.is_available,
    sort_order: props.product.sort_order || 0,
});

const errors = ref({});
const imageUrl = ref('');

function submit() {
    router.put(route('dashboard.products.update', props.product.id), form.value, {
        onError: (errs) => {
            errors.value = errs;
        },
    });
}

function addImage() {
    if (imageUrl.value) {
        form.value.images.push(imageUrl.value);
        imageUrl.value = '';
    }
}

function removeImage(index) {
    form.value.images.splice(index, 1);
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
                <h1 class="text-lg font-bold">Edit Product</h1>
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
                        <label class="block text-sm font-medium text-gray-700">Images (URLs)</label>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <div v-for="(img, index) in form.images" :key="index" class="relative h-16 w-16 rounded border">
                                <img :src="img" class="h-full w-full object-cover rounded" />
                                <button 
                                    type="button" 
                                    @click="removeImage(index)" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
                                >×</button>
                            </div>
                        </div>
                        <div class="mt-2 flex space-x-2">
                            <input 
                                v-model="imageUrl" 
                                type="url" 
                                placeholder="https://..." 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            />
                            <button 
                                type="button" 
                                @click="addImage" 
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm"
                            >Add</button>
                        </div>
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
