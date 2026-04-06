import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable for order management form
 * Handles form state, file uploads, and order updates
 */
export function useOrderForm(order) {
    const form = ref({
        status: order.status,
        payment_status: order.payment_status,
        shipping_cost: order.shipping_cost,
        payment_notes: order.payment_notes || '',
        adjustment_notes: order.adjustment_notes || '',
        shipping_receipt: null,
        notes: '', // For history log
    });

    const previewReceipt = ref(null);
    const showReminderModal = ref(false);

    /**
     * Handle file input change
     */
    function handleFileChange(event) {
        const file = event.target.files[0];
        if (file) {
            form.value.shipping_receipt = file;
            // Create preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewReceipt.value = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }

    /**
     * Trigger save — shows reminder modal first
     */
    function saveOrder() {
        showReminderModal.value = true;
    }

    /**
     * Confirm and execute save
     */
    async function confirmSaveOrder() {
        showReminderModal.value = false;

        const formData = new FormData();

        // Only include changed fields
        if (form.value.status !== order.status) {
            formData.append('status', form.value.status);
        }
        if (form.value.payment_status !== order.payment_status) {
            formData.append('payment_status', form.value.payment_status);
        }
        if (Number(form.value.shipping_cost) !== Number(order.shipping_cost)) {
            formData.append('shipping_cost', form.value.shipping_cost);
        }
        if (form.value.payment_notes !== (order.payment_notes || '')) {
            formData.append('payment_notes', form.value.payment_notes);
        }
        if (form.value.adjustment_notes !== (order.adjustment_notes || '')) {
            formData.append('adjustment_notes', form.value.adjustment_notes);
        }
        if (form.value.shipping_receipt) {
            formData.append('shipping_receipt', form.value.shipping_receipt);
        }
        if (form.value.notes) {
            formData.append('notes', form.value.notes);
        }

        // Add _method for PATCH
        formData.append('_method', 'PATCH');

        try {
            await router.post(`/dashboard/orders/${order.id}`, formData, {
                forceFormData: true,
                onError: (errors) => {
                    console.error('Validation errors:', errors);
                    alert('Failed to update order. Please check your input.');
                },
            });
        } catch (error) {
            console.error('Save order error:', error);
            alert('Failed to update order');
        }
    }

    return {
        form,
        previewReceipt,
        showReminderModal,
        handleFileChange,
        saveOrder,
        confirmSaveOrder,
    };
}
