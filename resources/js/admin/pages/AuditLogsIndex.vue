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
            <label class="filt">
                操作类型筛选
                <input v-model="action" type="text" placeholder="如 update、create" />
            </label>
        </template>
        <p v-if="err" class="bad">{{ err }}</p>
        <AdminCard>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>用户</th>
                        <th>操作</th>
                        <th>模型</th>
                        <th>ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="mono">{{ r.created_at?.replace('T', ' ')?.slice(0, 19) }}</td>
                        <td>{{ r.user?.name || '—' }}</td>
                        <td>{{ enumLabel('auditAction', r.action) }}</td>
                        <td class="small">{{ r.model_type }}</td>
                        <td>{{ r.model_id }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
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
.filt {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0;
    font-size: 0.85rem;
}
.filt input {
    padding: 0.35rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    width: 10rem;
}
.bad {
    color: #b91c1c;
}
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}
.tbl th,
.tbl td {
    padding: 0.45rem 0.55rem;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
    vertical-align: top;
}
.tbl th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
    white-space: nowrap;
}
.small {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
}
</style>
