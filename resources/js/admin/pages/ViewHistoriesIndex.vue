<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const rows = ref([]);
const meta = ref(null);
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
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <div>
        <h1 class="page-title">浏览历史（全站）</h1>
        <p class="lead">对齐功能清单 26：后台可审计 view_histories，与前台「我的浏览」同源数据。</p>
        <div class="toolbar">
            <input
                v-model="userId"
                type="search"
                class="search"
                placeholder="按 user_id 筛选"
                @change="load(1)"
            />
            <button type="button" class="btn" @click="load(1)">筛选</button>
        </div>
        <p v-if="err" class="err">{{ err }}</p>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户</th>
                        <th>类型</th>
                        <th>对象 ID</th>
                        <th>浏览时间</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>
                            <template v-if="r.user"> {{ r.user.name }}（{{ r.user.email }}） </template>
                            <template v-else>—</template>
                        </td>
                        <td class="mono">{{ r.viewable_type?.split('\\').pop() }}</td>
                        <td>{{ r.viewable_id }}</td>
                        <td class="muted">{{ r.viewed_at?.replace('T', ' ').slice(0, 19) }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无记录</p>
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
.toolbar {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}
.search {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    width: 200px;
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
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
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
