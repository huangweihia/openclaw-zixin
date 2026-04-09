<script setup>
defineProps({
    padded: { type: Boolean, default: false },
});
</script>

<template>
    <div class="oc-admin-card" :class="{ 'oc-admin-card--padded': padded }">
        <slot />
    </div>
</template>

<style scoped>
.oc-admin-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: auto;
}
.oc-admin-card--padded {
    padding: 1rem;
}
</style>

