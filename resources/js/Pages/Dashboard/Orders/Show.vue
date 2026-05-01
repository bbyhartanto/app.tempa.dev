<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { useOrderItems } from '@/composables/useOrderItems';
import { useOrderForm } from '@/composables/useOrderForm';
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
            preserveScroll: true,
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
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Mark as paid error:', error);
        alert('Failed to mark order as paid');
    }
}

function handleBack() {
    router.visit('/dashboard/orders');
}

const getStatusStyles = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-50 text-yellow-600 border-yellow-100';
        case 'confirmed': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'processing': return 'bg-purple-50 text-purple-600 border-purple-100';
        case 'shipped': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        case 'completed': return 'bg-green-50 text-green-600 border-green-100';
        case 'cancelled': return 'bg-red-50 text-red-600 border-red-100';
        default: return 'bg-gray-50 text-gray-600 border-gray-100';
    }
};

const getPaymentStyles = (status) => {
    return status === 'paid' ? 'bg-green-50 text-green-600 border-green-100' : 'bg-orange-50 text-orange-600 border-orange-100';
};
</script>

<template>
    <Head :title="`Order - ${order.order_number}`" />

    <div class="min-h-screen bg-white font-sans text-gray-900 pb-12">
        <!-- Header -->
        <header class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <button @click="handleBack" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <div class="space-y-0.5">
                    <h1 class="text-lg font-black tracking-tight">#{{ order.order_number }}</h1>
                    <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest">{{ order.created_at_formatted }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span 
                    class="px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider border shadow-sm"
                    :class="getStatusStyles(order.status)"
                >
                    {{ order.status }}
                </span>
            </div>
        </header>

        <main class="max-w-md mx-auto px-6 py-8 space-y-8">
            <!-- Customer Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-xl border border-blue-100/50">
                        👤
                    </div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">Customer Info</h2>
                </div>

                <div class="grid grid-cols-2 gap-y-6 gap-x-4">
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest">Name</p>
                        <p class="text-[15px] font-bold text-gray-900">{{ order.customer_name }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest">Phone</p>
                        <p class="text-[15px] font-bold text-gray-900">{{ order.customer_phone }}</p>
                    </div>
                    <div v-if="order.customer_instagram" class="space-y-1">
                        <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest">Instagram</p>
                        <p class="text-[15px] font-bold text-gray-900">{{ order.customer_instagram }}</p>
                    </div>
                    <div v-if="order.shipping_address && order.module_type !== 'booking'" class="col-span-2 space-y-1">
                        <p class="text-[11px] font-black text-gray-300 uppercase tracking-widest">Shipping Address</p>
                        <p class="text-[15px] font-bold text-gray-900 leading-relaxed">{{ order.shipping_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking Specific Card -->
            <div v-if="order.module_type === 'booking'" class="bg-purple-50/50 border-2 border-purple-100 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.02)] space-y-4">
                <div class="flex items-center space-x-3 text-purple-900">
                    <span class="text-xl">📅</span>
                    <h2 class="text-lg font-black tracking-tight">Booking Schedule</h2>
                </div>
                <div class="flex flex-col space-y-4 bg-white/60 p-5 rounded-[24px] border border-purple-100/50">
                    <div class="flex justify-between items-center">
                        <div class="space-y-1">
                            <p class="text-[11px] font-black text-purple-300 uppercase tracking-widest">Date</p>
                            <p class="text-[15px] font-bold text-purple-900">{{ order.booking_date }}</p>
                        </div>
                        <div class="text-right space-y-1">
                            <p class="text-[11px] font-black text-purple-300 uppercase tracking-widest">Time Slot</p>
                            <p class="text-[15px] font-bold text-purple-900">{{ order.booking_time_slot }}</p>
                        </div>
                    </div>
                    <div v-if="order.booking_duration_min" class="pt-3 border-t border-purple-100/30">
                        <p class="text-[11px] font-black text-purple-300 uppercase tracking-widest">Estimated Duration</p>
                        <p class="text-[15px] font-bold text-purple-900">{{ order.booking_duration_min }} minutes</p>
                    </div>
                </div>
            </div>

            <!-- Items Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] overflow-hidden shadow-[0_12px_40px_rgb(0,0,0,0.03)]">
                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-xl border border-orange-100/50">
                                📦
                            </div>
                            <h2 class="text-lg font-black tracking-tight text-gray-900">Order Items</h2>
                        </div>
                        <span v-if="order.is_modified" class="bg-orange-50 text-orange-600 text-[10px] font-black uppercase px-2 py-0.5 rounded-lg border border-orange-100">MODIFIED</span>
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
                </div>

                <div class="bg-gray-50/50 p-6 border-t-2 border-gray-50">
                    <PricingSummary :order="order" :show-payment-status="true" />
                </div>
            </div>

            <!-- Management Card -->
            <div class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-xl border border-purple-100/50">
                        ⚡
                    </div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">Manage Order</h2>
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
            </div>

            <!-- History Card -->
            <div v-if="histories.length" class="bg-white border-2 border-gray-50 rounded-[12px] p-6 shadow-[0_12px_40px_rgb(0,0,0,0.03)] space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-xl border border-gray-100/50">
                        📜
                    </div>
                    <h2 class="text-lg font-black tracking-tight text-gray-900">Order History</h2>
                </div>
                <div class="pl-2">
                    <OrderHistory :histories="histories" />
                </div>
            </div>
        </main>

        <ReminderModal
            v-if="showReminderModal"
            @cancel="showReminderModal = false"
            @confirm="confirmSaveOrder"
        />
    </div>
</template>

<style scoped>
.transition-all {
    transition-duration: 250ms;
}
</style>
