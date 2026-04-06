<template>
    <div class="mb-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Order history</h2>

        <div class="space-y-4">
            <div
                v-for="history in histories"
                :key="history.id"
                class="flex items-start gap-4"
            >
                <!-- Timeline Line -->
                <div class="flex flex-col items-center">
                    <div class="w-px h-12 bg-gray-300"></div>
                </div>

                <!-- Content -->
                <div class="flex-1 pb-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ history.action_label }}</p>
                            <p class="text-sm text-gray-500 mt-0.5">By: {{ history.changed_by }}</p>
                        </div>
                        <p class="text-xs text-gray-400 whitespace-nowrap">{{ history.created_at_formatted }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    histories: {
        type: Array,
        required: true,
    },
});
</script>
