<template>
    <div class="space-y-6">
        <!-- Accept Order CTA (when status is pending) -->
        <div v-if="order.status === 'pending'" class="bg-amber-50/50 border-2 border-amber-100 rounded-[24px] p-6 shadow-sm">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-2xl shadow-sm border border-amber-100/50">
                    📦
                </div>
                <div class="flex-1">
                    <h3 class="text-[16px] font-black text-amber-900 mb-1 leading-tight">Confirmation Required</h3>
                    <p class="text-[13px] font-bold text-amber-700/80 mb-4 leading-relaxed">This order is waiting for your approval to begin processing.</p>
                    <button
                        @click="$emit('accept-order')"
                        class="w-full sm:w-auto px-8 py-3 bg-amber-600 text-white font-black text-[14px] uppercase tracking-wider rounded-xl hover:bg-amber-700 transition-all active:scale-95 shadow-lg shadow-amber-200"
                    >
                        Accept Order
                    </button>
                </div>
            </div>
        </div>

        <!-- Mark as Paid CTA (when status is confirmed) -->
        <div v-if="order.status === 'confirmed'" class="bg-blue-50/50 border-2 border-blue-100 rounded-[24px] p-6 shadow-sm">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-2xl shadow-sm border border-blue-100/50">
                    💰
                </div>
                <div class="flex-1">
                    <h3 class="text-[16px] font-black text-blue-900 mb-1 leading-tight">Payment Received?</h3>
                    <p class="text-[13px] font-bold text-blue-700/80 mb-4 leading-relaxed">Has the customer completed the payment? Mark it as paid to proceed.</p>
                    <button
                        @click="$emit('mark-as-paid')"
                        class="w-full sm:w-auto px-8 py-3 bg-blue-600 text-white font-black text-[14px] uppercase tracking-wider rounded-xl hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-200"
                    >
                        Mark as Paid
                    </button>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <!-- Status Select -->
            <div v-if="order.status !== 'pending' && order.status !== 'confirmed'" class="space-y-2">
                <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Order Status</label>
                <div class="relative">
                    <select
                        :value="form.status"
                        @input="$emit('update:form', { ...form, status: $event.target.value })"
                        class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white px-5 py-4 rounded-2xl text-[15px] font-bold outline-none transition-all appearance-none cursor-pointer"
                    >
                        <option v-for="(label, value) in statusOptions" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-5 flex items-center pointer-events-none text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            <!-- Shipping Cost -->
            <div class="space-y-2">
                <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Shipping Cost</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none font-bold text-gray-400 group-focus-within:text-black">
                        Rp
                    </div>
                    <input
                        :value="form.shipping_cost"
                        @input="$emit('update:form', { ...form, shipping_cost: Number($event.target.value) })"
                        type="number"
                        min="0"
                        class="w-full bg-gray-50 border-2 border-transparent focus:border-gray-100 focus:bg-white pl-12 pr-5 py-4 rounded-2xl text-[15px] font-bold outline-none transition-all"
                    />
                </div>
            </div>

            <!-- Save Button -->
            <button
                @click="$emit('save')"
                class="w-full py-5 bg-black text-white rounded-[24px] font-black text-[16px] hover:bg-gray-800 transition-all active:scale-95 shadow-xl shadow-gray-100 mt-2"
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
