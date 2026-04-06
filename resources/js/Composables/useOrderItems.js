import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable for order item operations
 * Handles quantity updates, item removal, and restoration
 */
export function useOrderItems(order) {
    const editing = ref(null);
    const editQuantity = ref(0);
    const editNotes = ref('');

    const baseUrl = (itemId) => `/dashboard/orders/${order.id}/items/${itemId}`;

    const headers = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json',
    };

    /**
     * Generic API call wrapper for item operations
     */
    async function apiCall(url, method, body) {
        try {
            const res = await fetch(url, {
                method,
                headers,
                body: JSON.stringify(body),
            });
            const result = await res.json();

            if (result.success) {
                router.reload();
            } else {
                alert(result.message || 'Operation failed');
            }
        } catch (error) {
            console.error('API call error:', error);
            alert('Operation failed. Please try again.');
        }
    }

    /**
     * Start editing an item's quantity
     */
    function startEdit(item) {
        editing.value = item.id;
        editQuantity.value = item.current_quantity;
        editNotes.value = '';
    }

    /**
     * Cancel editing
     */
    function cancelEdit() {
        editing.value = null;
        editNotes.value = '';
    }

    /**
     * Save updated quantity
     */
    async function saveQuantity(item) {
        await apiCall(
            `${baseUrl(item.id)}/quantity`,
            'PATCH',
            { quantity: editQuantity.value, notes: editNotes.value }
        );
    }

    /**
     * Remove item from order
     */
    async function removeItem(item) {
        if (!confirm(`Remove "${item.product_name}" from this order?`)) return;
        await apiCall(baseUrl(item.id), 'DELETE', { notes: 'Removed by store - out of stock' });
    }

    /**
     * Restore removed item
     */
    async function restoreItem(item) {
        await apiCall(`${baseUrl(item.id)}/restore`, 'POST', { notes: 'Restored by store' });
    }

    return {
        editing,
        editQuantity,
        editNotes,
        startEdit,
        cancelEdit,
        saveQuantity,
        removeItem,
        restoreItem,
    };
}
