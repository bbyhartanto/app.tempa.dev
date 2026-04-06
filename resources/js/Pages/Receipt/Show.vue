<script setup>
import { Head } from '@inertiajs/vue3';
import ReceiptHeader from '@/Components/orders/ReceiptHeader.vue';
import StatusBanner from '@/Components/orders/StatusBanner.vue';
import CustomerInfo from '@/Components/orders/CustomerInfo.vue';
import OrderCard from '@/Components/orders/OrderCard.vue';
import PaymentNotes from '@/Components/orders/PaymentNotes.vue';
import AdjustmentNotes from '@/Components/orders/AdjustmentNotes.vue';
import ShippingReceipt from '@/Components/orders/ShippingReceipt.vue';
import OrderHistory from '@/Components/orders/OrderHistory.vue';
import ReceiptFooter from '@/Components/orders/ReceiptFooter.vue';

defineProps({
    tenant: { type: Object, required: true },
    order: { type: Object, required: true },
    items: { type: Array, required: true },
    histories: { type: Array, required: true },
});

const print = () => window.print();
</script>

<template>
    <Head :title="`Receipt - ${order.order_number}`" />

    <div class="min-h-screen bg-white">
        <div class="max-w-md mx-auto px-4 py-8">
            <ReceiptHeader :tenant="tenant" :order="order" />
            <StatusBanner :order="order" />
            <CustomerInfo :order="order" />

            <OrderCard :items="items" :order="order" @confirm-payment="print">
                <template #extras>
                    <PaymentNotes v-if="order.payment_notes" :notes="order.payment_notes" />
                    <AdjustmentNotes v-if="order.adjustment_notes" :notes="order.adjustment_notes" />
                    <ShippingReceipt v-if="order.shipping_receipt" :path="order.shipping_receipt" />
                </template>
            </OrderCard>

            <OrderHistory v-if="histories.length" :histories="histories" />
            <ReceiptFooter />
        </div>
    </div>
</template>

<style scoped>
@media print {
    button {
        display: none;
    }
}
</style>
