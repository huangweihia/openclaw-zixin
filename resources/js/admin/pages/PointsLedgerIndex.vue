<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const userId = ref('');
const loadErr = ref('');
const loading = ref(false);

async function load(page = 1) {
    loading.value = true;
    loadErr.value = '';
    try {
        const { data } = await axios.get('/api/admin/points-ledger', {
            params: {
                page,
                user_id: userId.value.trim() || undefined,
            },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch {
        loadErr.value = '无法加载积分流水（请确认已执行迁移且存在 points 表）';
        rows.value = [];
        meta.value = null;
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(1));

function onFilter() {
    load(1);
}
</script>

<template>
    <AdminPageShell title="积分流水" lead="表 points，含注册赠送等入账记录。" :loading="loading">
        <template #toolbar>
            <el-input
                v-model="userId"
                clearable
                placeholder="按用户 ID 筛选"
                style="width: 200px"
                @keyup.enter="onFilter"
            />
            <el-button type="primary" @click="onFilter">筛选</el-button>
        </template>
        <el-alert v-if="loadErr" type="error" :closable="false" show-icon class="oc-pl-alert" :title="loadErr" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无流水">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column label="用户" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">
                        <template v-if="row.user">{{ row.user.name }} · {{ row.user.email }}</template>
                        <template v-else>—</template>
                    </template>
                </el-table-column>
                <el-table-column prop="amount" label="变动" width="100" />
                <el-table-column prop="balance" label="余额" width="100" />
                <el-table-column prop="type" label="类型" width="120" />
                <el-table-column prop="category" label="分类" width="120" />
                <el-table-column prop="description" label="说明" min-width="160" show-overflow-tooltip />
                <el-table-column label="时间" width="168">
                    <template #default="{ row }">
                        <span class="mono">{{ row.created_at }}</span>
                    </template>
                </el-table-column>
            </el-table>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :loading="loading"
            @update:page="load"
        />
    </AdminPageShell>
</template>

<style scoped>
.oc-pl-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 12px;
}
</style>
