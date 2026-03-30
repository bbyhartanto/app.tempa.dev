<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useWhatsAppOrder } from '@/Composables/useWhatsAppOrder';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    product: {
        type: Object,
        required: true,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

const {
    customerName,
    customerPhone,
    shippingAddress,
    notes,
    generateMessage,
    generateWaLink,
    validateForm,
} = useWhatsAppOrder();

const quantity = ref(1);
const showCheckout = ref(false);

function increment() {
    quantity.value++;
}

function decrement() {
    if (quantity.value > 1) {
        quantity.value--;
    }
}

function buyNow() {
    showCheckout.value = true;
}

function sendToWhatsApp() {
    const validation = validateForm();
    
    if (!validation.isValid) {
        alert('Please fill in all required fields');
        return;
    }

    const orderItems = [{ ...props.product, quantity: quantity.value }];
    const message = generateMessage(orderItems, props.tenant);
    const waLink = generateWaLink(message, props.tenant);

    window.open(waLink, '_blank');
}
</script>

<template>
    <Head :title="product.title" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-3xl mx-auto px-4 py-4">
                <Link :href="route('storefront.home', { store_link: tenant.store_link })" class="flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to {{ tenant.name }}
                </Link>
            </div>
        </header>

        <!-- Product Detail -->
        <main class="max-w-3xl mx-auto px-4 py-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <!-- Product Images -->
                <div class="aspect-square bg-gray-100">
                    <img v-if="product.images?.[0]" :src="product.images[0]" :alt="product.title" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <svg class="h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="p-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ product.title }}</h1>
                    
                    <div v-if="templateConfig.layout?.show_prices !== false" class="mt-2">
                        <p class="text-3xl font-bold" :style="{ color: templateConfig.colors?.primary }">
                            {{ product.formatted_price }}
                        </p>
                    </div>

                    <!-- Availability -->
                    <div class="mt-2">
                        <span :class="product.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                            {{ product.is_available ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>

                    <!-- Description -->
                    <div v-if="templateConfig.layout?.show_description !== false && product.description" class="mt-4">
                        <h3 class="font-medium text-gray-900 mb-2">Description</h3>
                        <p class="text-gray-600 whitespace-pre-line">{{ product.description }}</p>
                    </div>

                    <!-- Quantity Selector -->
                    <div v-if="product.is_available" class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <div class="flex items-center space-x-4">
                            <button @click="decrement" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200">
                                <span class="text-xl">−</span>
                            </button>
                            <span class="text-xl font-medium w-12 text-center">{{ quantity }}</span>
                            <button @click="increment" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200">
                                <span class="text-xl">+</span>
                            </button>
                        </div>
                    </div>

                    <!-- Buy Button -->
                    <button
                        v-if="product.is_available"
                        @click="buyNow"
                        class="mt-6 w-full py-4 bg-green-600 text-white font-bold text-lg rounded-lg hover:bg-green-700 active:bg-green-800"
                    >
                        Buy via WhatsApp
                    </button>
                    <p v-else class="mt-6 text-center text-gray-500">
                        This product is currently unavailable
                    </p>
                </div>
            </div>

            <!-- Seller Info -->
            <div class="mt-6 bg-white rounded-lg shadow-sm p-4">
                <h3 class="font-medium text-gray-900 mb-3">Sold by</h3>
                <div class="flex items-center space-x-3">
                    <div v-if="tenant.logo_url" class="h-12 w-12">
                        <img :src="tenant.logo_url" :alt="tenant.name" class="h-full w-full object-contain rounded" />
                    </div>
                    <div class="flex-1">
                        <p class="font-medium">{{ tenant.name }}</p>
                        <p v-if="tenant.city" class="text-sm text-gray-500">📍 {{ tenant.city }}</p>
                    </div>
                </div>
                
                <a v-if="tenant.whatsapp_number" :href="`https://wa.me/${tenant.formatted_whatsapp_number}`" target="_blank" class="mt-3 block w-full py-2 border border-green-600 text-green-600 text-center font-medium rounded-lg hover:bg-green-50">
                    Chat with Seller
                </a>
            </div>
        </main>

        <!-- Checkout Modal -->
        <div v-if="showCheckout" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black bg-opacity-50" @click="showCheckout = false" />
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                    <h2 class="text-xl font-bold mb-4">Checkout</h2>
                    
                    <form @submit.prevent="sendToWhatsApp">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name *</label>
                                <input v-model="customerName" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                <input v-model="customerPhone" type="tel" required placeholder="+62..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Shipping Address *</label>
                                <textarea v-model="shippingAddress" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                                <textarea v-model="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="mt-6 border-t pt-4">
                            <h3 class="font-medium mb-2">Order Summary</h3>
                            <div class="flex justify-between text-sm">
                                <span>{{ product.title }} x{{ quantity }}</span>
                                <span>{{ product.formatted_price }} x{{ quantity }}</span>
                            </div>
                            <div class="flex justify-between font-bold pt-2 border-t mt-2">
                                <span>Total</span>
                                <span>{{ product.formatted_price }} x{{ quantity }}</span>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-3">
                            <button type="button" @click="showCheckout = false" class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                                Send to WhatsApp
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
