<script setup>
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { useOrderItems } from '@/composables/useOrderItems';
import { useOrderForm } from '@/composables/useOrderForm';
import OrderHeader from '@/Components/orders/OrderHeader.vue';
import CustomerInfo from '@/Components/orders/CustomerInfo.vue';
import OrderItems from '@/Components/orders/OrderItems.vue';
import OrderItemRow from '@/Components/orders/OrderItemRow.vue';
import OrderManagementForm from '@/Components/orders/OrderManagementForm.vue';
import PricingSummary from '@/Components/orders/PricingSummary.vue';
import OrderHistory from '@/Components/orders/OrderHistory.vue';
import ReminderModal from '@/Components/orders/ReminderModal.vue';

const props = defineProps({
    order: { type: Object, required: true },
    items: { type: Array, required: true },
    histories: { type: Array, required: true },
    statusOptions: { type: Object, required: true },
});

const {
    editing,
    editQuantity,
    editNotes,
    startEdit,
    cancelEdit,
    saveQuantity,
    removeItem,
    restoreItem,
} = useOrderItems(props.order);

const {
    form,
    previewReceipt,
    showReminderModal,
    saveOrder,
    confirmSaveOrder,
    handleFileChange,
} = useOrderForm(props.order);

/**
 * Accept order - change status from pending to confirmed
 */
async function acceptOrder() {
    if (!confirm('Accept this order and move it to confirmed status?')) {
        return;
    }

    try {
        await router.put(`/dashboard/orders/${props.order.id}/accept`, {}, {
            onSuccess: () => {
                // Page will reload with updated status
            },
            onError: (errors) => {
                console.error('Failed to accept order:', errors);
                alert('Failed to accept order. Please try again.');
            },
        });
    } catch (error) {
        console.error('Accept order error:', error);
        alert('Failed to accept order');
    }
}

/**
 * Mark order as paid - change status from confirmed to paid
 */
async function markAsPaid() {
    if (!confirm('Mark this order as paid (Telah Lunas)?')) {
        return;
    }

    try {
        await router.put(`/dashboard/orders/${props.order.id}/mark-paid`, {}, {
            onSuccess: () => {
                // Page will reload with updated status
            },
            onError: (errors) => {
                console.error('Failed to mark order as paid:', errors);
                alert('Failed to mark order as paid. Please try again.');
            },
        });
    } catch (error) {
        console.error('Mark as paid error:', error);
        alert('Failed to mark order as paid');
    }
}
</script>

<template>
    <Head :title="`Order - ${order.order_number}`" />

    <div class="min-h-screen bg-gray-50">
        <OrderHeader :order="order" />

        <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">
            <CustomerInfo :order="order" />

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Order Items</h2>
                    <span v-if="order.is_modified" class="text-sm text-orange-600 font-medium">
                        ⚠️ Order Modified
                    </span>
                </div>

                <div class="space-y-4">
                    <OrderItemRow
                        v-for="item in items"
                        :key="item.id"
                        :item="item"
                        :is-editing="editing === item.id"
                        :edit-quantity="editQuantity"
                        :edit-notes="editNotes"
                        @start-edit="startEdit(item)"
                        @cancel-edit="cancelEdit"
                        @save-quantity="saveQuantity(item)"
                        @remove="removeItem(item)"
                        @restore="restoreItem(item)"
                        @update:edit-quantity="editQuantity = $event"
                        @update:edit-notes="editNotes = $event"
                    />
                </div>

                <PricingSummary :order="order" :show-payment-status="false" />
            </div>

            <OrderManagementForm
                v-model:form="form"
                :order="order"
                :status-options="statusOptions"
                :preview-receipt="previewReceipt"
                @save="saveOrder"
                @file-change="handleFileChange"
                @accept-order="acceptOrder"
                @mark-as-paid="markAsPaid"
            />

            <OrderHistory v-if="histories.length" :histories="histories" />
        </main>

        <ReminderModal
            v-if="showReminderModal"
            @cancel="showReminderModal = false"
            @confirm="confirmSaveOrder"
        />
    </div>
</template>
