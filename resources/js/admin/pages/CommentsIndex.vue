<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const filter = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(30);
const loading = ref(false);
const err = ref('');

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const params = { page };
        if (filter.value === 'hidden') {
            params.hidden = '1';
        }
        if (filter.value === 'visible') {
            params.hidden = '0';
        }
        params.per_page = perPage.value;
        const { data } = await axios.get('/api/admin/comments', { params });
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

watch(filter, () => load(1));

async function toggleHide(c) {
    err.value = '';
    try {
        await axios.patch(`/api/admin/comments/${c.id}`, { is_hidden: !c.is_hidden });
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '操作失败';
    }
}

async function remove(c) {
    if (!confirm('确定删除该评论？')) {
        return;
    }
    err.value = '';
    try {
        await axios.delete(`/api/admin/comments/${c.id}`);
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '删除失败';
    }
}

function typeLabel(t) {
    if (!t) {
        return '—';
    }
    const s = t.split('\\').pop();
    return s || t;
}

function onPerPageChange(next) {
    perPage.value = Number(next) || 30;
    load(1);
}
</script>

<template>
    <AdminPageShell title="评论管理" lead="支持按可见性筛选、分页、隐藏/删除操作。">
        <template #toolbar>
            <el-tabs v-model="filter" class="tabs" type="card">
                <el-tab-pane label="全部" name="" />
                <el-tab-pane label="可见" name="visible" />
                <el-tab-pane label="已隐藏" name="hidden" />
            </el-tabs>
        </template>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>用户</th>
                        <th>目标</th>
                        <th>内容</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="c in rows" :key="c.id">
                        <td>{{ c.id }}</td>
                        <td>{{ c.user?.name || c.user?.email || '—' }}</td>
                        <td class="small">
                            {{ typeLabel(c.commentable_type) }} #{{ c.commentable_id }}
                            <div class="muted">{{ c.target_title }}</div>
                        </td>
                        <td class="content">{{ c.content }}</td>
                        <td>{{ c.is_hidden ? '隐藏' : '可见' }}</td>
                        <td class="actions">
                            <button type="button" class="link" @click="toggleHide(c)">
                                {{ c.is_hidden ? '显示' : '隐藏' }}
                            </button>
                            <button type="button" class="link danger" @click="remove(c)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无评论</p>
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
.tabs {
    margin-bottom: 0;
}
.err {
    color: #b91c1c;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.table th,
.table td {
    padding: 0.55rem 0.65rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.content {
    max-width: 280px;
    word-break: break-word;
}
.small {
    font-size: 0.78rem;
}
.muted {
    color: #64748b;
    margin-top: 0.2rem;
}
.actions {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    text-align: left;
}
.link.danger {
    color: #b91c1c;
}
.empty {
    padding: 1.25rem;
    color: #64748b;
    margin: 0;
}
</style>
