<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const route = useRoute();
const router = useRouter();
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const perPage = ref(20);
const err = ref('');
let searchT;

const publishedFilter = ref('');

function syncFilterFromRoute() {
    const p = route.query.published;
    publishedFilter.value = p === '0' || p === '1' ? p : '';
}

async function load(page = 1) {
    err.value = '';
    try {
        const params = { page, per_page: perPage.value, q: q.value.trim() || undefined };
        if (publishedFilter.value === '0' || publishedFilter.value === '1') {
            params.published = publishedFilter.value;
        }
        const { data } = await axios.get('/api/admin/articles', { params });
        rows.value = data.data ?? [];
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

function setPublishedFilter(v) {
    const query = { ...route.query };
    if (v === '') {
        delete query.published;
    } else {
        query.published = v;
    }
    router.replace({ query });
}

onMounted(() => {
    syncFilterFromRoute();
    load(1);
});

watch(
    () => route.query.published,
    () => {
        syncFilterFromRoute();
        load(1);
    }
);

function onSearch() {
    clearTimeout(searchT);
    searchT = setTimeout(() => load(1), 300);
}

async function removeRow(id) {
    if (!confirm('确定删除该文章？')) {
        return;
    }
    try {
        await axios.delete(`/api/admin/articles/${id}`);
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
    <AdminPageShell title="文章管理" lead="支持草稿/已发布筛选，支持搜索与分页。">
        <template #actions>
            <button type="button" class="btn primary" @click="router.push({ name: 'article-create' })">新建文章</button>
        </template>
        <template #toolbar>
            <nav class="tabs">
                <button type="button" class="tab" :class="{ on: publishedFilter === '' }" @click="setPublishedFilter('')">
                    全部
                </button>
                <button type="button" class="tab" :class="{ on: publishedFilter === '1' }" @click="setPublishedFilter('1')">
                    已发布
                </button>
                <button type="button" class="tab" :class="{ on: publishedFilter === '0' }" @click="setPublishedFilter('0')">
                    草稿
                </button>
            </nav>
            <input v-model="q" class="search" type="search" placeholder="标题 / slug / 摘要" @input="onSearch" />
        </template>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>标题</th>
                        <th>分类</th>
                        <th>发布</th>
                        <th>会员专享</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in rows" :key="a.id">
                        <td>{{ a.id }}</td>
                        <td>{{ a.title }}</td>
                        <td>{{ a.category?.name || '—' }}</td>
                        <td>{{ a.is_published ? '是' : '否' }}</td>
                        <td>{{ a.is_vip ? '是' : '否' }}</td>
                        <td class="actions">
                            <button type="button" class="link" @click="router.push({ name: 'article-edit', params: { id: a.id } })">
                                编辑
                            </button>
                            <a class="link" :href="`/articles/${a.slug}`" target="_blank" rel="noopener">前台</a>
                            <button type="button" class="link danger" @click="removeRow(a.id)">删除</button>
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
            :per-page="perPage"
            @update:page="load"
            @update:per-page="onPerPageChange"
        />
    </AdminPageShell>
</template>

<style scoped>
.tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin: 0;
}
.tab {
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: 0.82rem;
}
.tab.on {
    background: #1e293b;
    color: #fff;
    border-color: #1e293b;
}
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
    color: #475569;
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
