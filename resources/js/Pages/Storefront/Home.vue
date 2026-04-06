<script setup>
import { ref, toRef } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';
import { useCheckout } from '@/Composables/useCheckout';
import FloatingCartButton from '@/Components/Storefront/FloatingCartButton.vue';
import CartDrawer from '@/Components/Storefront/CartDrawer.vue';
import CheckoutModal from '@/Components/Storefront/CheckoutModal.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        required: true,
        default: () => [],
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

// Use shared cart store
const cart = useCartStore;

// Cart drawer state
const showCartDrawer = ref(false);

function openCartDrawer() {
    showCartDrawer.value = true;
}

function closeCartDrawer() {
    showCartDrawer.value = false;
}

// Use checkout composable
const tenantRef = toRef(props, 'tenant');
const {
    showCheckoutForm,
    showSuccessModal,
    lastOrderNumber,
    lastReceiptUrl,
    handleCheckout,
    openCheckout,
    closeSuccessModal,
} = useCheckout(tenantRef, cart.formatCurrency, cart.clearCart);
</script>

<template>
    <Head :title="tenant.name" />

    <div class="min-h-screen bg-white">
        <!-- Header Section with Yellow Background -->
        <header class="bg-[#FFC947] px-5 py-8 pb-12">
            <div class="max-w-md mx-auto">
                <!-- Store Info -->
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h1 class="text-4xl font-bold text-black leading-tight mb-2">
                            {{ tenant.name }}
                        </h1>
                        <div class="flex items-center text-black text-base">
                            <span>Bandung</span>
                            <a href="#" class="ml-2 underline">maps</a>
                            <span class="ml-1">📍</span>
                        </div>
                    </div>
                    
                    <!-- Logo -->
                    <div v-if="tenant.logo_url" class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 flex-shrink-0 ml-4">
                        <img :src="tenant.logo_url" :alt="tenant.name" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0 ml-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>

                <!-- Description -->
                <p v-if="tenant.description" class="text-black text-base leading-relaxed mb-8">
                    {{ tenant.description }}
                </p>

                <!-- External Links -->
                <div class="space-y-3">
                    <template v-if="tenant.store_links && tenant.store_links.length > 0">
                        <a
                            v-for="link in tenant.store_links"
                            :key="link.label"
                            :href="link.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">{{ link.label }}</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                    </template>

                    <template v-else>
                        <a
                            href="#"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">Grabfood link</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                        <a
                            href="#"
                            class="block w-full py-4 px-5 bg-[#FFD76E] border-2 border-white rounded-full hover:bg-[#FFE08A] transition"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-black text-lg">Gofood link</span>
                                <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7m10 0v10" />
                                </svg>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </header>

        <!-- Products Section -->
        <section class="max-w-md mx-auto px-5 py-8 -mt-6 bg-white rounded-t-3xl">
            <h2 class="text-2xl font-bold text-black mb-6">Products</h2>

            <div v-if="products.length === 0" class="text-center py-12">
                <p class="text-gray-500">No products available yet.</p>
            </div>

            <div v-else class="grid grid-cols-2 gap-x-4 gap-y-6">
                <div v-for="product in products" :key="product.id" class="flex flex-col">
                    <!-- Product Image -->
                    <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })" class="block">
                        <div v-if="product.first_image" class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                            <img :src="product.first_image" :alt="product.title" class="w-full h-full object-cover" />
                        </div>
                        <div v-else class="aspect-square bg-gray-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Product Info -->
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <Link :href="route('storefront.products.show', { store_link: tenant.store_link, productSlug: product.slug })">
                                <h3 class="font-medium text-black text-sm leading-snug line-clamp-2">{{ product.title }}</h3>
                            </Link>
                            <p class="text-gray-500 text-sm mt-1">{{ product.formatted_price }}</p>
                        </div>
                        
                        <!-- Add Button -->
                        <button
                            @click="cart.addToCart(product)"
                            class="flex-shrink-0 w-10 h-10 bg-[#FF8C42] rounded-full flex items-center justify-center hover:bg-[#FF9D5C] active:bg-[#FF7A33] transition"
                        >
                            <svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- View All Products Link -->
            <div v-if="products.length > 0" class="text-center mt-8">
                <Link 
                    :href="route('storefront.catalog', { store_link: tenant.store_link })"
                    class="text-black text-lg underline font-medium hover:opacity-70 transition"
                >
                    Lihat semua produk
                </Link>
            </div>

            <!-- Bottom spacing for floating button -->
            <div class="h-20"></div>
        </section>

        <!-- Floating Cart Button -->
        <FloatingCartButton
            v-if="!cart.isEmpty"
            :cart-count="cart.cartCount"
            @click="openCartDrawer"
        />

        <!-- Cart Drawer -->
        <CartDrawer
            :show="showCartDrawer"
            :cart-items="cart.cartItems"
            :cart-total="cart.cartTotal"
            :cart-count="cart.cartCount"
            :currency="products[0]?.currency || 'IDR'"
            @close="closeCartDrawer"
            @remove-item="cart.removeFromCart"
            @update-quantity="({ productId, quantity }) => cart.updateQuantity(productId, quantity)"
            @checkout="() => { closeCartDrawer(); openCheckout(); }"
        />

        <!-- Checkout Modal -->
        <CheckoutModal
            :show="showCheckoutForm"
            :cart-items="cart.cartItems"
            :tenant="tenant"
            :show-success="showSuccessModal"
            :order-number="lastOrderNumber"
            :receipt-url="lastReceiptUrl"
            @close="showCheckoutForm = false"
            @submit="handleCheckout"
            @close-success="closeSuccessModal"
        />
    </div>
</template>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
