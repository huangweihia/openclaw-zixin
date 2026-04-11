<script setup>
import { computed } from 'vue';

/**
 * 与 Laravel 分页 JSON 对齐：current_page、last_page、total、per_page（可选）。
 * @event update:page 页码变更（1-based）
 */
const props = defineProps({
    currentPage: { type: Number, required: true },
    lastPage: { type: Number, required: true },
    total: { type: Number, default: null },
    perPage: { type: Number, default: 20 },
    pageSizeOptions: { type: Array, default: () => [10, 20, 50, 100] },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:page', 'update:per-page']);

const displayTotal = computed(() => {
    if (props.total != null && Number.isFinite(props.total)) {
        return props.total;
    }
    if (props.lastPage > 0 && props.perPage > 0) {
        return props.lastPage * props.perPage;
    }
    return 0;
});

function onPageChange(p) {
    emit('update:page', p);
}

function onSizeChange(s) {
    emit('update:per-page', s);
}
</script>

<template>
    <el-pagination
        v-if="lastPage > 1 || (total != null && total > perPage)"
        class="oc-admin-pagination"
        background
        layout="total, sizes, prev, pager, next, jumper"
        :page-sizes="pageSizeOptions"
        :current-page="currentPage"
        :page-size="perPage"
        :total="displayTotal"
        :disabled="loading"
        @current-change="onPageChange"
        @size-change="onSizeChange"
    />
</template>

<style scoped>
.oc-admin-pagination {
    width: 100%;
    flex-wrap: wrap;
    row-gap: 8px;
}
</style>
