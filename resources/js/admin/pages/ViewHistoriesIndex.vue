<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');
const userId = ref('');

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/view-histories', {
            params: {
                page,
                user_id: userId.value.trim() || undefined,
            },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="浏览历史（全站）" lead="对齐功能清单 26：后台可审计 view_histories，与前台「我的浏览」同源数据。">
        <template #toolbar>
            <el-input
                v-model="userId"
                clearable
                placeholder="按 user_id 筛选"
                style="width: 200px"
                @change="load(1)"
            />
            <el-button type="primary" @click="load(1)">筛选</el-button>
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-vh-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无记录">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column label="用户" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">
                        <template v-if="row.user">{{ row.user.name }}（{{ row.user.email }}）</template>
                        <template v-else>—</template>
                    </template>
                </el-table-column>
                <el-table-column label="类型" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="mono">{{ row.viewable_type?.split('\\').pop() }}</span>
                    </template>
                </el-table-column>
                <el-table-column prop="viewable_id" label="对象 ID" width="100" />
                <el-table-column label="浏览时间" width="168">
                    <template #default="{ row }">
                        <span class="muted">{{ row.viewed_at?.replace('T', ' ').slice(0, 19) }}</span>
                    </template>
                </el-table-column>
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
.oc-vh-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
.muted {
    color: var(--el-text-color-secondary);
    white-space: nowrap;
}
</style>
