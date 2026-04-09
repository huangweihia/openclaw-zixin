<script setup>
/**
 * 与 Laravel 分页 JSON 对齐：current_page、last_page、total、per_page（可选）。
 * @event update:page 页码变更（1-based）
 */
defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    total: { type: Number, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:page']);

function go(p) {
    emit('update:page', p);
}
</script>

<template>
    <div v-if="lastPage > 1" class="oc-admin-pager">
        <button type="button" class="oc-admin-pager__btn" :disabled="loading || currentPage <= 1" @click="go(currentPage - 1)">
            上一页
        </button>
        <span class="oc-admin-pager__meta">
            第 {{ currentPage }} / {{ lastPage }} 页
            <template v-if="total != null"> · 共 {{ total }} 条</template>
        </span>
        <button
            type="button"
            class="oc-admin-pager__btn"
            :disabled="loading || currentPage >= lastPage"
            @click="go(currentPage + 1)"
        >
            下一页
        </button>
    </div>
</template>
<style scoped>
.oc-admin-pager {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.75rem 1rem;
    margin-top: 1rem;
    font-size: 0.88rem;
    color: #475569;
}
.oc-admin-pager__btn {
    padding: 0.4rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: inherit;
}
.oc-admin-pager__btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.oc-admin-pager__meta {
    white-space: nowrap;
}
</style>

