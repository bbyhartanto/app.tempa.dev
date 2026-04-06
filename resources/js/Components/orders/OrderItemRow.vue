<template>
    <div
        class="border rounded-lg p-4"
        :class="{
            'opacity-50 bg-gray-50': item.is_removed,
            'border-orange-200 bg-orange-50': !item.is_removed && item.original_quantity !== item.current_quantity,
        }"
    >
        <!-- Edit Mode -->
        <div v-if="isEditing">
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input
                        :value="editQuantity"
                        @input="$emit('update:edit-quantity', Number($event.target.value))"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (for history)</label>
                    <input
                        :value="editNotes"
                        @input="$emit('update:edit-notes', $event.target.value)"
                        type="text"
                        placeholder="e.g., Out of stock, will restock"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 text-sm"
                    />
                </div>
                <div class="flex gap-2">
                    <button
                        @click="$emit('save-quantity')"
                        class="flex-1 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium"
                    >
                        Save
                    </button>
                    <button
                        @click="$emit('cancel-edit')"
                        class="flex-1 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- View Mode -->
        <div v-else>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-gray-900">{{ item.product_name }}</h3>
                        <span v-if="item.is_removed" class="text-xs text-red-600 font-medium">(REMOVED)</span>
                        <span v-else-if="item.original_quantity !== item.current_quantity" class="text-xs text-orange-600 font-medium">(ADJUSTED)</span>
                    </div>

                    <p v-if="item.product_sku" class="text-xs text-gray-500 mt-1">SKU: {{ item.product_sku }}</p>

                    <div class="mt-2 text-sm">
                        <div v-if="item.is_removed" class="text-red-600">
                            <p>❌ Removed - Original quantity: {{ item.original_quantity }}</p>
                        </div>
                        <div v-else-if="item.original_quantity !== item.current_quantity" class="text-orange-600">
                            <p>⚠️ Quantity adjusted: {{ item.original_quantity }} → {{ item.current_quantity }}</p>
                        </div>
                        <div v-else class="text-gray-600">
                            <p>Quantity: {{ item.current_quantity }}</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mt-1">{{ formatCurrency(item.unit_price, item.currency) }} each</p>
                </div>

                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">{{ item.formatted_subtotal }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 mt-3 pt-3 border-t">
                <button
                    v-if="!item.is_removed"
                    @click="$emit('start-edit')"
                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Edit Qty
                </button>
                <button
                    v-if="!item.is_removed"
                    @click="$emit('remove')"
                    class="px-3 py-1 text-sm bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                    Remove
                </button>
                <button
                    v-else
                    @click="$emit('restore')"
                    class="px-3 py-1 text-sm bg-green-600 text-white rounded-md hover:bg-green-700"
                >
                    Restore
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { formatCurrency } from '@/utils/currency';

defineProps({
    item: {
        type: Object,
        required: true,
    },
    isEditing: {
        type: Boolean,
        default: false,
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
