<script setup>
import { ref, computed, toRef } from 'vue';
import { Head } from '@inertiajs/vue3';
import { useCartStore } from '@/Stores/cartStore';
import { useCheckout } from '@/Composables/useCheckout';
import CatalogHeader from '@/Components/Storefront/CatalogHeader.vue';
import CatalogGrid from '@/Components/Storefront/CatalogGrid.vue';
import FloatingCartButton from '@/Components/Storefront/FloatingCartButton.vue';
import CartDrawer from '@/Components/Storefront/CartDrawer.vue';
import CheckoutModal from '@/Components/Storefront/CheckoutModal.vue';

const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
    products: {
        type: Object,
        required: true,
    },
    templateConfig: {
        type: Object,
        required: true,
    },
});

// Use shared cart store
const cart = useCartStore;

// Use checkout composable (pass cart store methods)
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

// Pagination state
const allProducts = ref(props.products.data || []);
const currentPage = ref(props.products.current_page || 1);
const lastPage = ref(props.products.last_page || 1);
const hasMore = ref(props.products.current_page < props.products.last_page);
const loading = ref(false);
const storeLink = props.tenant.store_link;
const totalProducts = ref(props.products.total || 0);

// Cart drawer state (UI only, not part of shared store)
const showCartDrawer = ref(false);

function openCartDrawer() {
    showCartDrawer.value = true;
}

function closeCartDrawer() {
    showCartDrawer.value = false;
}

// Load more products (infinite scroll)
async function loadMoreProducts() {
    if (loading.value || !hasMore.value) return;

    loading.value = true;
    const nextPage = currentPage.value + 1;

    try {
        const response = await fetch(`/${props.tenant.store_link}/catalog/products?page=${nextPage}`, {
            headers: {
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (data.products && data.products.length > 0) {
            allProducts.value = [...allProducts.value, ...data.products];
            currentPage.value = data.current_page;
            lastPage.value = data.last_page;
            hasMore.value = data.has_more;
        } else {
            hasMore.value = false;
        }
    } catch (error) {
        console.error('Error loading more products:', error);
    } finally {
        loading.value = false;
    }
}

function handleFloatingCartClick() {
    if (cartItems.value.length > 0) {
        openCartDrawer();
    }
}
</script>

<template>
    <Head :title="tenant.name" />

    <div class="min-h-screen bg-white pb-24">
        <!-- Header -->
        <CatalogHeader
            :store-name="tenant.name"
            :product-count="totalProducts"
            :store-link="storeLink"
        />

        <!-- Products Grid with Infinite Scroll -->
        <main class="max-w-md mx-auto px-4 py-6">
            <CatalogGrid
                :products="allProducts"
                :store-link="storeLink"
                :loading="loading"
                :has-more="hasMore"
                @add-to-cart="cart.addToCart"
                @load-more="loadMoreProducts"
            />
        </main>

        <!-- Floating Cart Button -->
        <FloatingCartButton
            :cart-count="cart.cartCount"
            @click="openCartDrawer"
        />

        <!-- Cart Drawer -->
        <CartDrawer
            :show="showCartDrawer"
            :cart-items="cart.cartItems"
            :cart-total="cart.cartTotal"
            :cart-count="cart.cartCount"
            :currency="allProducts[0]?.currency || 'IDR'"
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
