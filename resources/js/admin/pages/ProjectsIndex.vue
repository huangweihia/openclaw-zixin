<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const err = ref('');
let searchT;

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/projects', {
            params: { page, q: q.value.trim() || undefined },
        });
        rows.value = data.data ?? [];
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
</script>

<template>
    <div>
        <div class="head">
            <h1 class="page-title">项目管理</h1>
            <button type="button" class="btn primary" @click="router.push({ name: 'project-create' })">新建项目</button>
        </div>
        <input v-model="q" class="search" type="search" placeholder="名称 / 仓库 / URL" @input="onSearch" />
        <p v-if="err" class="err">{{ err }}</p>
        <div class="card">
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
.head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.page-title {
    margin: 0;
    font-size: 1.5rem;
}
.search {
    width: 100%;
    max-width: 360px;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    overflow: auto;
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
.pager {
    margin-top: 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: center;
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
