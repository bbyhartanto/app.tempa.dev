<script setup>
const props = defineProps({
    service: {
        type: Object,
        required: true,
    },
    showDescription: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'small', // 'small' or 'large'
        validator: (value) => ['small', 'large'].includes(value),
    },
});
</script>

<template>
    <div :class="[
        'flex',
        size === 'large' ? 'flex-col' : 'flex-row gap-4',
        'items-start'
    ]">
        <!-- Service Image -->
        <div :class="[
            'flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden',
            size === 'large' 
                ? 'w-full h-48 mb-4' 
                : 'w-20 h-20'
        ]">
            <img
                v-if="service.first_image"
                :src="service.first_image"
                :alt="service.name"
                class="w-full h-full object-cover"
            />
            <div
                v-else
                class="w-full h-full flex items-center justify-center"
            >
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- Service Info -->
        <div class="min-w-0">
            <h3 class="font-bold text-black text-base leading-snug mb-1">
                {{ service.name }}
            </h3>

            <p class="font-bold text-green-600 text-sm">
                {{ service.formatted_price }}
            </p>

            <!-- Duration & Buffer -->
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ service.duration_min }} min</span>
                </div>
                <span v-if="service.buffer_min">•</span>
                <div v-if="service.buffer_min" class="flex items-center gap-1">
                    <span>+{{ service.buffer_min }} min buffer</span>
                </div>
            </div>
        </div>
    </div>
</template>
