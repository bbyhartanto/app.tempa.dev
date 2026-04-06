import { ref, computed } from 'vue';

/**
 * useCart Composable
 * 
 * Manages cart state across the storefront.
 * Can be used in any component: Home, Catalog, Product pages.
 * Cart persists in memory during the session.
 */
export function useCart() {
    const cart = ref([]);
    const showCartDrawer = ref(false);

    // Computed
    const cartTotal = computed(() => {
        return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    });

    const cartCount = computed(() => {
        return cart.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    const cartItems = computed(() => cart.value);

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

    function clearCart() {
        cart.value = [];
    }

    function openCartDrawer() {
        showCartDrawer.value = true;
    }

    function closeCartDrawer() {
        showCartDrawer.value = false;
    }

    // Format currency helper
    function formatCurrency(amount, currency = 'IDR') {
        if (currency === 'IDR') {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }
        return `${currency} ${Number(amount).toFixed(2)}`;
    }

    return {
        // State
        cart,
        showCartDrawer,
        // Computed
        cartTotal,
        cartCount,
        cartItems,
        // Actions
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        openCartDrawer,
        closeCartDrawer,
        formatCurrency,
    };
}
