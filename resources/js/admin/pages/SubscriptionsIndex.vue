<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/subscriptions', { params: { page } });
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
    <AdminPageShell title="会员订阅记录" lead="subscriptions 表联 users。">
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-sub-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无订阅记录">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column label="用户" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user_name }}（{{ row.user_email }}）
                    </template>
                </el-table-column>
                <el-table-column label="套餐" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('subscriptionPlan', row.plan) }}
                    </template>
                </el-table-column>
                <el-table-column prop="amount" label="金额" width="100" />
                <el-table-column label="状态" width="120">
                    <template #default="{ row }">
                        {{ enumLabel('subscriptionStatus', row.status) }}
                    </template>
                </el-table-column>
                <el-table-column label="到期" width="120">
                    <template #default="{ row }">
                        <span class="muted">{{ row.expires_at?.slice(0, 10) || '—' }}</span>
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
.oc-sub-alert {
    margin-bottom: 12px;
}
.muted {
    color: var(--el-text-color-secondary);
    white-space: nowrap;
}
</style>
