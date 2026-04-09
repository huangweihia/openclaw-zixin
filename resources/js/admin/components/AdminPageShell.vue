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
            <div>
                <h1 class="oc-admin-page__title">{{ title }}</h1>
                <p v-if="lead" class="oc-admin-page__lead">{{ lead }}</p>
            </div>
            <div class="oc-admin-page__actions">
                <slot name="actions" />
            </div>
        </div>

        <div v-if="$slots.toolbar" class="oc-admin-page__toolbar">
            <slot name="toolbar" />
        </div>

        <div class="oc-admin-page__body" :class="{ 'is-loading': loading }">
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
.oc-admin-page__title {
    margin: 0;
    font-size: 1.5rem;
}
.oc-admin-page__lead {
    margin: 0.35rem 0 0;
    color: #64748b;
    font-size: 0.88rem;
    line-height: 1.55;
}
.oc-admin-page__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
}
.oc-admin-page__toolbar {
    margin: 0.75rem 0 0.85rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    align-items: center;
}
.oc-admin-page__body.is-loading {
    opacity: 0.85;
}
</style>

