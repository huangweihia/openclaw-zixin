<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const router = useRouter();
const q = ref('');
const users = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(20);
const loadErr = ref('');
const loading = ref(false);
const role = ref('');
const isBanned = ref('');

async function load(page = 1) {
    loading.value = true;
    loadErr.value = '';
    try {
        const { data } = await axios.get('/api/admin/users', {
            params: {
                page,
                per_page: perPage.value,
                q: q.value || undefined,
                role: role.value || undefined,
                is_banned: isBanned.value === '' ? undefined : isBanned.value,
            },
        });
        users.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch {
        loadErr.value = '无法加载用户列表';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load(1));

let searchTimer;
function onSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => load(1), 350);
}

function goEdit(id) {
    router.push({ name: 'user-edit', params: { id: String(id) } });
}

function onPerPageChange(next) {
    perPage.value = Number(next) || 20;
    load(1);
}
</script>

<template>
    <AdminPageShell title="用户管理" lead="支持按角色/状态筛选，点击编辑进入详细配置页。">
        <template #toolbar>
            <input
                v-model="q"
                type="search"
                class="search"
                placeholder="按邮箱或昵称搜索"
                autocomplete="off"
                @input="onSearchInput"
            />
            <select v-model="role" class="search" @change="load(1)">
                <option value="">全部角色</option>
                <option value="user">普通用户</option>
                <option value="vip">VIP</option>
                <option value="svip">SVIP</option>
                <option value="admin">管理员</option>
            </select>
            <select v-model="isBanned" class="search" @change="load(1)">
                <option value="">全部状态</option>
                <option :value="'0'">正常</option>
                <option :value="'1'">已禁用</option>
            </select>
        </template>
        <p v-if="loadErr" class="msg-err">{{ loadErr }}</p>
        <AdminCard class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>昵称</th>
                        <th>邮箱</th>
                        <th>角色</th>
                        <th>积分</th>
                        <th>会员到期</th>
                        <th>企微</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id">
                        <td>{{ u.id }}</td>
                        <td>{{ u.name }}</td>
                        <td>{{ u.email }}</td>
                        <td>{{ enumLabel('userRole', u.role) }}</td>
                        <td>{{ Number(u.points_balance || 0) }}</td>
                        <td>{{ u.subscription_ends_at ? u.subscription_ends_at.slice(0, 10) : '—' }}</td>
                        <td>
                            <span v-if="u.enterprise_wechat_id" class="tag tag--ok">已授权</span>
                            <span v-else class="tag tag--muted">未授权</span>
                        </td>
                        <td>
                            <span v-if="u.is_banned" class="tag tag--bad">已禁用</span>
                            <span v-else class="tag tag--ok">正常</span>
                        </td>
                        <td>
                            <button type="button" class="link-btn" @click="goEdit(u.id)">编辑</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && users.length === 0" class="empty">暂无用户</p>
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
.search {
    min-width: 160px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
}
.msg-err {
    color: #b91c1c;
}
.table-wrap {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    overflow: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}
.table th,
.table td {
    padding: 0.65rem 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
}
.link-btn {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    font-size: inherit;
}
.tag {
    font-size: 0.75rem;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
}
.tag--ok {
    background: #dcfce7;
    color: #166534;
}
.tag--bad {
    background: #fee2e2;
    color: #991b1b;
}
.tag--muted {
    background: #f1f5f9;
    color: #64748b;
}
.empty {
    padding: 1.5rem;
    color: #64748b;
    margin: 0;
}
</style>
