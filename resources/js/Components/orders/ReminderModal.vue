<template>
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="$emit('cancel')" />

            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 mb-4">
                        <svg class="h-10 w-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 mb-2">Reminder</h3>
                    <p class="text-gray-600 mb-6">
                        Please chat your customer for updated changes regarding shipping method and costs.
                    </p>

                    <div class="flex gap-3">
                        <button
                            @click="$emit('cancel')"
                            class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="$emit('confirm')"
                            class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700"
                        >
                            Continue & Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineEmits(['cancel', 'confirm']);
</script>
