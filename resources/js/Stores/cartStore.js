import { reactive, computed } from 'vue';

/**
 * Global Cart Store
 * 
 * Shared cart state across all storefront pages (Home, Catalog, Product).
 * Uses Vue reactive for simple global state without external libraries.
 */
const state = reactive({
    items: [],
});

export const useCartStore = {
    state,

    // Computed
    get cartItems() {
        return state.items;
    },

    get cartCount() {
        return state.items.reduce((sum, item) => sum + item.quantity, 0);
    },

    get cartTotal() {
        return state.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    },

    get isEmpty() {
        return state.items.length === 0;
    },

    // Actions
    addToCart(product) {
        const existingItem = state.items.find(item => item.id === product.id);

        if (existingItem) {
            existingItem.quantity++;
        } else {
            state.items.push({
                ...product,
                quantity: 1,
            });
        }
    },

    removeFromCart(productId) {
        state.items = state.items.filter(item => item.id !== productId);
    },

    updateQuantity(productId, quantity) {
        const item = state.items.find(item => item.id === productId);
        if (item) {
            item.quantity = Math.max(1, quantity);
        }
    },

    clearCart() {
        state.items = [];
    },

    formatCurrency(amount, currency = 'IDR') {
        if (currency === 'IDR') {
            return 'Rp ' + Number(amount).toLocaleString('id-ID');
        }
        return `${currency} ${Number(amount).toFixed(2)}`;
    },
};
