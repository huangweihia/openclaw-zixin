<script setup>
defineProps({
    padded: { type: Boolean, default: false },
});
</script>

<template>
    <el-card class="oc-admin-card" shadow="hover" :body-style="{ padding: padded ? '20px' : '0' }">
        <slot />
    </el-card>
</template>

<style scoped>
.oc-admin-card {
    border-radius: 12px;
}
.oc-admin-card :deep(.el-card__body) {
    overflow-x: auto;
}
</style>
