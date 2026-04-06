<template>
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
                @start-edit="$emit('start-edit', item)"
                @cancel-edit="$emit('cancel-edit')"
                @save-quantity="$emit('save-quantity', item)"
                @remove="$emit('remove', item)"
                @restore="$emit('restore', item)"
                @update:edit-quantity="$emit('update:edit-quantity', $event)"
                @update:edit-notes="$emit('update:edit-notes', $event)"
            />
        </div>

        <!-- Pricing Summary -->
        <div class="border-t mt-6 pt-6 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Original Subtotal</span>
                <span class="font-medium">{{ formatCurrency(order.original_subtotal) }}</span>
            </div>

            <div v-if="order.original_subtotal !== order.adjusted_subtotal" class="flex justify-between text-sm text-orange-600">
                <span>Adjusted Subtotal</span>
                <span class="font-medium">{{ formatCurrency(order.adjusted_subtotal) }}</span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Shipping Cost</span>
                <span class="font-medium">{{ formatCurrency(order.shipping_cost) }}</span>
            </div>

            <div class="border-t pt-2 mt-2">
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span>{{ formatCurrency(order.total) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import OrderItemRow from './OrderItemRow.vue';
import { formatCurrency } from '@/utils/currency';

defineProps({
    items: {
        type: Array,
        required: true,
    },
    order: {
        type: Object,
        required: true,
    },
    editing: {
        type: [Number, null],
        default: null,
    },
    editQuantity: {
        type: Number,
        default: 0,
    },
    editNotes: {
        type: String,
        default: '',
    },
});

defineEmits([
    'start-edit',
    'cancel-edit',
    'save-quantity',
    'remove',
    'restore',
    'update:edit-quantity',
    'update:edit-notes',
]);
</script>
