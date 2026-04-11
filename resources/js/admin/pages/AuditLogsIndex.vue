<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const action = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/audit-logs', {
            params: { page, action: action.value.trim() || undefined },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

watch(action, () => load(1));

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="操作审计" lead="数据表 audit_logs（只读）。">
        <template #toolbar>
            <span class="oc-audit-filter-label">操作类型筛选</span>
            <el-input v-model="action" clearable placeholder="如 update、create" style="width: 200px" />
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-audit-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无">
                <el-table-column label="时间" width="168">
                    <template #default="{ row }">
                        <span class="mono">{{ row.created_at?.replace('T', ' ')?.slice(0, 19) }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="用户" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user?.name || '—' }}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('auditAction', row.action) }}
                    </template>
                </el-table-column>
                <el-table-column label="模型" min-width="160" show-overflow-tooltip prop="model_type" />
                <el-table-column prop="model_id" label="ID" width="88" />
            </el-table>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            @update:page="load"
        />
    </AdminPageShell>
</template>

<style scoped>
.oc-audit-filter-label {
    font-size: 13px;
    color: var(--el-text-color-regular);
}
.oc-audit-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
    white-space: nowrap;
}
</style>
