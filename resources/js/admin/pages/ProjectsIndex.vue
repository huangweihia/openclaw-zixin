<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const router = useRouter();
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(20);
const err = ref('');
let searchT;

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/projects', {
            params: { page, per_page: perPage.value, q: q.value.trim() || undefined },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

onMounted(() => load(1));

function onSearch() {
    clearTimeout(searchT);
    searchT = setTimeout(() => load(1), 300);
}

async function removeRow(id) {
    if (!confirm('确定删除该项目？')) {
        return;
    }
    try {
        await axios.delete(`/api/admin/projects/${id}`);
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '删除失败';
    }
}

function onPerPageChange(next) {
    perPage.value = Number(next) || 20;
    load(1);
}
</script>

<template>
    <AdminPageShell title="项目管理" lead="支持搜索与分页，点击编辑进入详情页。">
        <template #actions>
            <button type="button" class="btn primary" @click="router.push({ name: 'project-create' })">新建项目</button>
        </template>
        <template #toolbar>
            <input v-model="q" class="search" type="search" placeholder="名称 / 仓库 / URL" @input="onSearch" />
        </template>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>语言</th>
                        <th>星标数</th>
                        <th>分类</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in rows" :key="p.id">
                        <td>{{ p.id }}</td>
                        <td>{{ p.name }}</td>
                        <td>{{ p.language || '—' }}</td>
                        <td>{{ p.stars }}</td>
                        <td>{{ p.category?.name || '—' }}</td>
                        <td class="actions">
                            <button type="button" class="link" @click="router.push({ name: 'project-edit', params: { id: p.id } })">
                                编辑
                            </button>
                            <a class="link" :href="`/projects/${p.id}`" target="_blank" rel="noopener">前台</a>
                            <button type="button" class="link danger" @click="removeRow(p.id)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无数据</p>
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
    width: 100%;
    max-width: 360px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.err {
    color: #b91c1c;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
}
.table th,
.table td {
    padding: 0.6rem 0.85rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    font-size: inherit;
    text-decoration: none;
}
.link.danger {
    color: #b91c1c;
}
.empty {
    padding: 1.25rem;
    color: #64748b;
    margin: 0;
}
.btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: 0.9rem;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
</style>
