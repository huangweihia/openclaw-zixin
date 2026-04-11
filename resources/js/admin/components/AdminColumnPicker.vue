<script setup>
import { computed } from 'vue';

const props = defineProps({
    /** @type {import('vue').PropType<Array<{ key: string, label: string, field?: string }>>} */
    definitions: { type: Array, required: true },
    modelValue: { type: Array, required: true },
});

const emit = defineEmits(['update:modelValue', 'select-all', 'reset-default']);

const inner = computed({
    get: () => props.modelValue,
    set: (v) => emit('update:modelValue', v),
});
</script>

<template>
    <el-popover placement="bottom-end" :width="340" trigger="click">
        <template #reference>
            <el-button plain type="primary">列显示</el-button>
        </template>
        <div class="oc-col-picker">
            <div class="oc-col-picker__head">
                <span class="oc-col-picker__title">表格列</span>
                <div class="oc-col-picker__actions">
                    <el-button text type="primary" size="small" @click="emit('select-all')">全选</el-button>
                    <el-button text type="primary" size="small" @click="emit('reset-default')">默认</el-button>
                </div>
            </div>
            <el-text size="small" type="info" class="oc-col-picker__hint">
                勾选要在列表中展示的列；括号内为数据库字段名。设置保存在本机浏览器。
            </el-text>
            <el-scrollbar max-height="280px">
                <el-checkbox-group v-model="inner" class="oc-col-picker__group">
                    <div v-for="c in definitions" :key="c.key" class="oc-col-picker__row">
                        <el-checkbox :label="c.key">
                            <span class="oc-col-picker__label">{{ c.label }}</span>
                            <el-text v-if="c.field" tag="span" size="small" type="info" class="oc-col-picker__field">
                                （{{ c.field }}）
                            </el-text>
                        </el-checkbox>
                    </div>
                </el-checkbox-group>
            </el-scrollbar>
        </div>
    </el-popover>
</template>

<style scoped>
.oc-col-picker__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px;
}
.oc-col-picker__title {
    font-weight: 600;
    font-size: 14px;
    color: var(--el-text-color-primary);
}
.oc-col-picker__actions {
    display: flex;
    gap: 2px;
    flex-shrink: 0;
}
.oc-col-picker__hint {
    display: block;
    margin-bottom: 10px;
    line-height: 1.45;
}
.oc-col-picker__group {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 4px;
}
.oc-col-picker__row :deep(.el-checkbox) {
    height: auto;
    margin-right: 0;
    align-items: flex-start;
    white-space: normal;
}
.oc-col-picker__label {
    margin-right: 4px;
}
.oc-col-picker__field {
    font-weight: 400;
}
</style>
