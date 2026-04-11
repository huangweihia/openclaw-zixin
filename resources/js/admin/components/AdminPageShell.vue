<script setup>
defineProps({
    title: { type: String, required: true },
    lead: { type: String, default: '' },
    loading: { type: Boolean, default: false },
});
</script>

<template>
    <div class="oc-admin-page">
        <div class="oc-admin-page__head">
            <div class="oc-admin-page__titles">
                <h1 class="oc-admin-page__title">{{ title }}</h1>
                <el-text v-if="lead" type="info" class="oc-admin-page__lead">{{ lead }}</el-text>
            </div>
            <el-space wrap alignment="center" class="oc-admin-page__actions">
                <slot name="actions" />
            </el-space>
        </div>

        <div v-if="$slots.toolbar" class="oc-admin-page__toolbar">
            <slot name="toolbar" />
        </div>

        <div v-loading="loading" class="oc-admin-page__body">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.oc-admin-page__head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}
.oc-admin-page__titles {
    min-width: 0;
    flex: 1;
}
.oc-admin-page__title {
    margin: 0;
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--el-text-color-primary);
    letter-spacing: -0.02em;
    line-height: 1.3;
}
.oc-admin-page__lead {
    display: block;
    margin-top: 0.35rem;
    font-size: 0.875rem;
    line-height: 1.55;
    max-width: 52rem;
}
.oc-admin-page__actions {
    flex-shrink: 0;
}
.oc-admin-page__toolbar {
    margin: 0.75rem 0 0.85rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    align-items: center;
}
.oc-admin-page__body {
    min-height: 120px;
}
</style>
