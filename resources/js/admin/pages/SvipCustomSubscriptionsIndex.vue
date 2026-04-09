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
        const q = filterQuery.value.trim();
        if (q.length < 1) {
            filterSuggestions.value = [];
            return;
        }
        try {
            filterSuggestions.value = await searchUsers(q);
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
    }
}

onMounted(() => load(1));

function onPerPageChange(next) {
    perPage.value = Number(next) || 20;
    load(1);
}
</script>

<template>
    <AdminPageShell title="SVIP 定制订阅" lead="对齐功能清单 24：只读列表，数据表 svip_custom_subscriptions。">
        <template #toolbar>
            <div class="user-sel">
                <input
                    v-model="filterQuery"
                    type="search"
                    class="search user-sel__input"
                    placeholder="按用户搜索（昵称或邮箱）"
                    autocomplete="off"
                    @input="scheduleFilterSearch"
                />
                <ul v-if="filterSuggestions.length" class="user-sel__dd">
                    <li v-for="u in filterSuggestions" :key="'f-' + u.id" @click="pickFilterUser(u)">
                        {{ u.name }} &lt;{{ u.email }}&gt; <span class="uid">#{{ u.id }}</span>
                    </li>
                </ul>
            </div>
            <button v-if="filterUserId" type="button" class="btn btn-clear" @click="clearFilterUser">清除用户</button>
            <select v-model="status" class="search" @change="load(1)">
                <option v-for="o in statusOptions" :key="'st-' + (o.value || 'all')" :value="o.value">
                    {{ o.label }}
                </option>
            </select>
            <button type="button" class="btn" @click="load(1)">筛选</button>
        </template>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户</th>
                        <th>方案名</th>
                        <th>金额</th>
                        <th>服务天数</th>
                        <th>状态</th>
                        <th>到期</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>
                            <template v-if="r.user">
                                {{ r.user.name }}（{{ r.user.email }}）· {{ enumLabel('userRole', r.user.role) }}
                            </template>
                            <template v-else>—</template>
                        </td>
                        <td>{{ r.plan_name }}</td>
                        <td>{{ Number(r.amount) > 0 ? r.amount : '—' }}</td>
                        <td>{{ Number(r.duration_days) > 0 ? r.duration_days : '—' }}</td>
                        <td>{{ enumLabel('svipCustomSubscriptionStatus', r.status) }}</td>
                        <td class="muted">{{ r.expires_at?.slice(0, 10) || '—' }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无记录（表可为空）</p>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :per-page="perPage"
            @update:page="load"
            @update:per-page="onPerPageChange"
        />
    </AdminPageShell>
</template>

<style scoped>
.search {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    min-width: 140px;
}
.user-sel {
    position: relative;
    min-width: 200px;
    flex: 1;
    max-width: 320px;
}
.user-sel__input {
    width: 100%;
    box-sizing: border-box;
}
.user-sel__dd {
    list-style: none;
    margin: 0.2rem 0 0;
    padding: 0.2rem 0;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 5;
    max-height: 200px;
    overflow: auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.1);
}
.user-sel__dd li {
    padding: 0.35rem 0.55rem;
    cursor: pointer;
    font-size: 0.8rem;
}
.user-sel__dd li:hover {
    background: #f1f5f9;
}
.user-sel__dd .uid {
    color: #94a3b8;
    font-size: 0.72rem;
}
.btn-clear {
    font-size: 0.78rem;
    color: #475569;
}
.btn {
    padding: 0.45rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.table th,
.table td {
    padding: 0.5rem 0.65rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.muted {
    color: #64748b;
    white-space: nowrap;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
}
.pager {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
</style>
