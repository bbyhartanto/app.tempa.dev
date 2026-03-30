<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useWhatsAppOrder } from '@/Composables/useWhatsAppOrder';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

// WhatsApp order composable
const {
    customerName,
    customerPhone,
    shippingAddress,
    notes,
    generateMessage,
    generateWaLink,
    validateForm,
    resetForm,
} = useWhatsAppOrder();

// Cart state
const cart = ref([]);
const showCart = ref(false);
const showCheckout = ref(false);

// Computed
const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const cartCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

// Cart actions
function addToCart(product) {
    const existingItem = cart.value.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            ...product,
            quantity: 1,
        });
    }
}

function removeFromCart(productId) {
    cart.value = cart.value.filter(item => item.id !== productId);
}

function updateQuantity(productId, quantity) {
    const item = cart.value.find(item => item.id === productId);
    if (item) {
        item.quantity = Math.max(1, quantity);
    }
}

function openCheckout() {
    showCart.value = false;
    showCheckout.value = true;
}

// Checkout actions
function sendToWhatsApp() {
    const validation = validateForm();
    
    if (!validation.isValid) {
        alert('Please fill in all required fields');
        return;
    }

    const message = generateMessage(cart.value, props.tenant);
    const waLink = generateWaLink(message, props.tenant);

    // Open WhatsApp
    window.open(waLink, '_blank');

    // Reset after sending
    cart.value = [];
    showCheckout.value = false;
    resetForm();
}

// Format currency helper
function formatCurrency(amount, currency = 'IDR') {
    if (currency === 'IDR') {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
    return `${currency} ${Number(amount).toFixed(2)}`;
}
</script>

<template>
    <Head :title="tenant.name" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div v-if="tenant.logo_url" class="h-10 w-10">
                            <img :src="tenant.logo_url" :alt="tenant.name" class="h-full w-full object-contain" />
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">{{ tenant.name }}</h1>
                            <p v-if="tenant.description" class="text-sm text-gray-500 line-clamp-1">
                                {{ tenant.description }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Cart Button -->
                    <button
                        @click="showCart = true"
                        class="relative p-2 text-gray-600 hover:text-gray-900"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="cartCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            {{ cartCount }}
                        </span>
                    </button>
                </div>

                <!-- Store Status -->
                <div class="mt-2 flex items-center space-x-2">
                    <span :class="tenant.is_open_now ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-medium">
                        {{ tenant.is_open_now ? 'Open' : 'Closed' }}
                    </span>
                    <span v-if="tenant.city" class="text-xs text-gray-500">
                        📍 {{ tenant.city }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Products Grid -->
        <main class="max-w-7xl mx-auto px-4 py-6">
            <div v-if="products.length === 0" class="text-center py-12">
                <p class="text-gray-500">No products available yet.</p>
            </div>

            <div v-else :class="`grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4`">
                <div v-for="product in products" :key="product.id" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <!-- Product Image -->
                    <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })" class="block">
                        <div v-if="product.first_image" class="aspect-square bg-gray-100">
                            <img :src="product.first_image" :alt="product.title" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="aspect-square bg-gray-100 flex items-center justify-center">
                            <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Product Info -->
                    <div class="p-3">
                        <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })">
                            <h3 class="font-medium text-gray-900 text-sm line-clamp-2">{{ product.title }}</h3>
                        </Link>
                        
                        <div v-if="templateConfig.layout?.show_prices !== false" class="mt-1">
                            <p class="text-lg font-bold" :style="{ color: templateConfig.colors?.primary }">
                                {{ product.formatted_price }}
                            </p>
                        </div>

                        <!-- Add to Cart Button -->
                        <button
                            @click="addToCart(product)"
                            class="mt-2 w-full py-2 px-3 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-800 active:bg-gray-700"
                        >
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Cart Drawer -->
        <div v-if="showCart" class="fixed inset-0 z-50 overflow-hidden">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="showCart = false" />
            
            <div class="absolute inset-y-0 right-0 max-w-full flex">
                <div class="w-screen max-w-md bg-white shadow-xl flex flex-col h-full">
                    <!-- Cart Header -->
                    <div class="px-4 py-6 border-b">
                        <h2 class="text-lg font-bold">Shopping Cart ({{ cartCount }})</h2>
                        <button @click="showCart = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto px-4 py-4">
                        <div v-if="cart.length === 0" class="text-center text-gray-500 py-8">
                            Your cart is empty
                        </div>
                        
                        <div v-else v-for="item in cart" :key="item.id" class="flex items-center space-x-4 py-4 border-b">
                            <div class="flex-1">
                                <h4 class="font-medium text-sm">{{ item.title }}</h4>
                                <p class="text-gray-500 text-sm">{{ item.formatted_price }}</p>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button @click="updateQuantity(item.id, item.quantity - 1)" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="text-lg">−</span>
                                </button>
                                <span class="w-8 text-center">{{ item.quantity }}</span>
                                <button @click="updateQuantity(item.id, item.quantity + 1)" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="text-lg">+</span>
                                </button>
                            </div>
                            
                            <button @click="removeFromCart(item.id)" class="text-red-500 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Cart Footer -->
                    <div v-if="cart.length > 0" class="border-t px-4 py-6">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-gray-600">Total</span>
                            <span class="text-xl font-bold">{{ formatCurrency(cartTotal, products[0]?.currency) }}</span>
                        </div>
                        
                        <button
                            @click="openCheckout"
                            class="w-full py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700"
                        >
                            Checkout via WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>

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
                            <div class="space-y-1 text-sm">
                                <div v-for="item in cart" :key="item.id" class="flex justify-between">
                                    <span>{{ item.title }} x{{ item.quantity }}</span>
                                    <span>{{ formatCurrency(item.price * item.quantity, item.currency) }}</span>
                                </div>
                                <div class="flex justify-between font-bold pt-2 border-t mt-2">
                                    <span>Total</span>
                                    <span>{{ formatCurrency(cartTotal, products[0]?.currency) }}</span>
                                </div>
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

        <!-- Footer -->
        <footer class="bg-white border-t mt-8">
            <div class="max-w-7xl mx-auto px-4 py-6">
                <div class="text-center text-sm text-gray-500">
                    <p>Powered by E-Catalog SaaS</p>
                </div>
            </div>
        </footer>
    </div>
</template>
