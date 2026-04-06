<template>
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Management</h2>

        <div class="space-y-4">
            <!-- Accept Order CTA (when status is pending) -->
            <div v-if="order.status === 'pending'" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 text-2xl">📦</div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-amber-900 mb-1">Order Awaiting Confirmation</h3>
                        <p class="text-sm text-amber-700 mb-3">This order is waiting for your confirmation. Accept it to proceed with processing.</p>
                        <button
                            @click="$emit('accept-order')"
                            class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors"
                        >
                            ✓ Accept Order
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mark as Paid CTA (when status is confirmed) -->
            <div v-if="order.status === 'confirmed'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 text-2xl">💰</div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 mb-1">Payment Confirmation</h3>
                        <p class="text-sm text-blue-700 mb-3">Has the customer completed payment? Mark this order as paid.</p>
                        <button
                            @click="$emit('mark-as-paid')"
                            class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors"
                        >
                            ✓ Telah Lunas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div v-if="order.status !== 'pending' && order.status !== 'confirmed'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Order Status</label>
                <select
                    :value="form.status"
                    @input="$emit('update:form', { ...form, status: $event.target.value })"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                        {{ label }}
                    </option>
                </select>
            </div>

            <!-- Payment Status (Hidden) -->
            <div v-if="false">
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                <select
                    :value="form.payment_status"
                    @input="$emit('update:form', { ...form, payment_status: $event.target.value })"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                >
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                </select>
            </div>

            <!-- Shipping Cost -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost</label>
                <input
                    :value="form.shipping_cost"
                    @input="$emit('update:form', { ...form, shipping_cost: Number($event.target.value) })"
                    type="number"
                    min="0"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Payment Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Notes</label>
                <textarea
                    :value="form.payment_notes"
                    @input="$emit('update:form', { ...form, payment_notes: $event.target.value })"
                    rows="3"
                    placeholder="Payment instructions for customer"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Adjustment Notes (Hidden) -->
            <div v-if="false">
                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Notes</label>
                <textarea
                    :value="form.adjustment_notes"
                    @input="$emit('update:form', { ...form, adjustment_notes: $event.target.value })"
                    rows="3"
                    placeholder="Explain why order was modified (visible to customer)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                />
            </div>

            <!-- Shipping Receipt Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shipping Receipt</label>
                <input
                    type="file"
                    @change="$emit('file-change', $event)"
                    accept="image/*,.pdf"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm"
                />
                <p v-if="order.shipping_receipt && !previewReceipt" class="mt-1 text-sm text-gray-500">
                    Current: {{ order.shipping_receipt }}
                </p>
                <img
                    v-if="previewReceipt"
                    :src="previewReceipt"
                    alt="Preview"
                    class="mt-2 max-w-full h-auto rounded border"
                />
            </div>

            <!-- Notes for History Log (Hidden) -->
            <div v-if="false">
                <label class="block text-sm font-medium text-gray-700 mb-1">Change Notes (optional)</label>
                <input
                    :value="form.notes"
                    @input="$emit('update:form', { ...form, notes: $event.target.value })"
                    type="text"
                    placeholder="Brief description of changes (logged to history)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm"
                />
            </div>

            <!-- Save Button -->
            <button
                @click="$emit('save')"
                class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
            >
                Save Changes
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    form: {
        type: Object,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
    statusOptions: {
        type: Object,
        required: true,
    },
    previewReceipt: {
        type: [String, null],
        default: null,
    },
});

defineEmits([
    'update:form',
    'save',
    'file-change',
    'accept-order',
    'mark-as-paid',
]);
</script>
