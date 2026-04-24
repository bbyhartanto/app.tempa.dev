<script setup>
const props = defineProps({
    tenant: {
        type: Object,
        required: true,
    },
});

const backgroundColor = props.tenant.settings?.background_color || '#FFC947';
const backgroundImage = props.tenant.background_image ? `url('${props.tenant.background_image}')` : 'none';
const hasImage = backgroundImage !== 'none';
</script>

<template>
    <div
        :class="hasImage ? 'parallax-wrapper' : 'min-h-screen'"
        :style="{ backgroundColor: backgroundColor }"
    >
        <!-- Background Parallax Layer -->
        <div
            v-if="hasImage"
            class="parallax-layer-back"
            :style="{
                backgroundImage: backgroundImage,
                backgroundPosition: 'center',
                backgroundSize: 'cover',
                backgroundRepeat: 'no-repeat'
            }"
        ></div>

        <!-- Foreground Content -->
        <div :class="hasImage ? 'parallax-layer-base min-h-screen' : ''">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.parallax-wrapper {
    height: 100vh;
    overflow-x: hidden;
    overflow-y: auto;
    perspective: 1px;
}

.parallax-layer-back {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    height: 120vh;
    transform: translateZ(-1px) scale(2.4);
    z-index: -1;
    transform-origin: center center;
}

.parallax-layer-base {
    position: relative;
    z-index: 1;
}
</style>
