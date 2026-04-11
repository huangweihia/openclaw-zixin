<script setup>
import { computed, onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import AdminPagination from '../components/AdminPagination.vue';

const rows = ref([]);
const meta = ref(null);
const err = ref('');
const loading = ref(false);
const filterUserId = ref('');
const filterQuery = ref('');
const filterSuggestions = ref([]);
let filterTimer = null;
const status = ref('');
const perPage = ref(20);
const total = ref(0);

const statusOptions = computed(() => [{ value: '', label: '全部状态' }, ...enumOptions('svipCustomSubscriptionStatus')]);

async function searchUsers(q) {
    const { data } = await axios.get('/api/admin/users', { params: { q, page: 1 } });
    return data.data ?? [];
}

function scheduleFilterSearch() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(async () => {
        const qv = filterQuery.value.trim();
        if (qv.length < 1) {
            filterSuggestions.value = [];
            return;
        }
        try {
            filterSuggestions.value = await searchUsers(qv);
        } catch {
            filterSuggestions.value = [];
        }
    }, 280);
}

function pickFilterUser(u) {
    filterUserId.value = String(u.id);
    filterQuery.value = `${u.name} (${u.email})`;
    filterSuggestions.value = [];
    load(1);
}

function clearFilterUser() {
    filterUserId.value = '';
    filterQuery.value = '';
    filterSuggestions.value = [];
    load(1);
}

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/svip-custom-subscriptions', {
            params: {
                page,
                per_page: perPage.value,
                q: filterQuery.value.trim() || undefined,
                user_id: filterUserId.value.trim() || undefined,
                status: status.value || undefined,
            },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(1));

function onPerPageChange(next) {
    perPage.value = Number(next) || 20;
    load(1);
}

function onFilter() {
    load(1);
}
</script>

<template>
    <AdminPageShell title="SVIP 定制订阅" lead="对齐功能清单 24：只读列表，数据表 svip_custom_subscriptions。">
        <template #toolbar>
            <div class="user-sel">
                <el-input
                    v-model="filterQuery"
                    clearable
                    placeholder="按用户搜索（昵称或邮箱）"
                    class="user-sel__input"
                    @input="scheduleFilterSearch"
                    @clear="clearFilterUser"
                />
                <ul v-if="filterSuggestions.length" class="user-sel__dd">
                    <li v-for="u in filterSuggestions" :key="'f-' + u.id" @click="pickFilterUser(u)">
                        {{ u.name }} &lt;{{ u.email }}&gt; <span class="uid">#{{ u.id }}</span>
                    </li>
                </ul>
            </div>
            <el-button v-if="filterUserId" @click="clearFilterUser">清除用户</el-button>
            <el-select v-model="status" placeholder="状态" style="width: 160px" @change="load(1)">
                <el-option v-for="o in statusOptions" :key="'st-' + (o.value || 'all')" :label="o.label" :value="o.value" />
            </el-select>
            <el-button type="primary" @click="onFilter">筛选</el-button>
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
        <AdminCard>
            <el-table v-loading="loading" :data="rows" stripe border style="width: 100%" empty-text="暂无记录（表可为空）">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column label="用户" min-width="220" show-overflow-tooltip>
                    <template #default="{ row }">
                        <template v-if="row.user">
                            {{ row.user.name }}（{{ row.user.email }}）· {{ enumLabel('userRole', row.user.role) }}
                        </template>
                        <template v-else>—</template>
                    </template>
                </el-table-column>
                <el-table-column prop="plan_name" label="方案名" min-width="120" show-overflow-tooltip />
                <el-table-column label="金额" width="100">
                    <template #default="{ row }">{{ Number(row.amount) > 0 ? row.amount : '—' }}</template>
                </el-table-column>
                <el-table-column label="服务天数" width="100">
                    <template #default="{ row }">{{ Number(row.duration_days) > 0 ? row.duration_days : '—' }}</template>
                </el-table-column>
                <el-table-column label="状态" width="120">
                    <template #default="{ row }">{{ enumLabel('svipCustomSubscriptionStatus', row.status) }}</template>
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
            :per-page="perPage"
            :loading="loading"
            @update:page="load"
            @update:per-page="onPerPageChange"
        />
    </AdminPageShell>
</template>

<style scoped>
.user-sel {
    position: relative;
    min-width: 200px;
    flex: 1;
    max-width: 320px;
}
.user-sel__input {
    width: 100%;
}
.user-sel__dd {
    list-style: none;
    margin: 4px 0 0;
    padding: 4px 0;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 10;
    max-height: 200px;
    overflow: auto;
    background: var(--el-bg-color-overlay);
    border: 1px solid var(--el-border-color-light);
    border-radius: var(--el-border-radius-base);
    box-shadow: var(--el-box-shadow-light);
}
.user-sel__dd li {
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
}
.user-sel__dd li:hover {
    background: var(--el-fill-color-light);
}
.user-sel__dd .uid {
    color: var(--el-text-color-secondary);
    font-size: 11px;
}
.oc-c-alert {
    margin-bottom: 12px;
}
.muted {
    color: var(--el-text-color-secondary);
    white-space: nowrap;
}
</style>
