<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';

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
    <div>
        <h1 class="page-title">积分流水</h1>
        <p class="lead">表 <code>points</code>，含注册赠送等入账记录。</p>
        <div class="toolbar">
            <input v-model="userId" type="search" class="search" placeholder="按用户 ID 筛选" @keyup.enter="onFilter" />
            <button type="button" class="btn" @click="onFilter">筛选</button>
        </div>
        <p v-if="loadErr" class="msg-err">{{ loadErr }}</p>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户</th>
                        <th>变动</th>
                        <th>余额</th>
                        <th>类型</th>
                        <th>分类</th>
                        <th>说明</th>
                        <th>时间</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>
                            <span v-if="r.user">{{ r.user.name }} · {{ r.user.email }}</span>
                            <span v-else>—</span>
                        </td>
                        <td>{{ r.amount }}</td>
                        <td>{{ r.balance }}</td>
                        <td>{{ r.type }}</td>
                        <td>{{ r.category }}</td>
                        <td class="desc">{{ r.description }}</td>
                        <td class="mono sm">{{ r.created_at }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!loading && rows.length === 0" class="empty">暂无流水</p>
        </div>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :loading="loading"
            @update:page="load"
        />
    </div>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.88rem;
    color: #64748b;
}
.lead code {
    font-size: 0.85em;
    background: #e2e8f0;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}
.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.search {
    width: 100%;
    max-width: 220px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.btn {
    padding: 0.5rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.msg-err {
    color: #b91c1c;
}
.table-wrap {
    background: #fff;
    border-radius: 10px;
    overflow: auto;
    border: 1px solid #e2e8f0;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table th,
.table td {
    padding: 0.55rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.desc {
    max-width: 240px;
    word-break: break-word;
}
.mono {
    font-family: ui-monospace, monospace;
    white-space: nowrap;
}
.sm {
    font-size: 0.78rem;
}
.empty {
    padding: 1.25rem;
    color: #94a3b8;
    margin: 0;
}
</style>
