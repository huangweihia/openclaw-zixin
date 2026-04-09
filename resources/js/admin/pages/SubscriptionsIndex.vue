<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel } from '../constants/labels';

const rows = ref([]);
const meta = ref(null);
const err = ref('');

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/subscriptions', { params: { page } });
        rows.value = data.data ?? [];
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <div>
        <h1 class="page-title">会员订阅记录</h1>
        <p class="lead">对应文档「会员权益管理」后台数据：subscriptions 表联 users。</p>
        <p v-if="err" class="err">{{ err }}</p>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户</th>
                        <th>套餐</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>到期</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>{{ r.user_name }}（{{ r.user_email }}）</td>
                        <td>{{ enumLabel('subscriptionPlan', r.plan) }}</td>
                        <td>{{ r.amount }}</td>
                        <td>{{ enumLabel('subscriptionStatus', r.status) }}</td>
                        <td class="muted">{{ r.expires_at?.slice(0, 10) || '—' }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无订阅记录</p>
        </div>
        <div v-if="meta && meta.last_page > 1" class="pager">
            <button type="button" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">上一页</button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button type="button" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                下一页
            </button>
        </div>
    </div>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
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
    gap: 0.75rem;
    align-items: center;
}
</style>
