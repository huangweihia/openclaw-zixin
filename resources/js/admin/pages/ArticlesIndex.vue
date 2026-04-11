<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
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
    try {
        await ElMessageBox.confirm('确定删除该文章？', '删除文章', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
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
            <el-button type="primary" @click="router.push({ name: 'article-create' })">新建文章</el-button>
        </template>
        <template #toolbar>
            <el-radio-group :model-value="publishedFilter" size="default" @update:model-value="setPublishedFilter">
                <el-radio-button value="">全部</el-radio-button>
                <el-radio-button value="1">已发布</el-radio-button>
                <el-radio-button value="0">草稿</el-radio-button>
            </el-radio-group>
            <el-input
                v-model="q"
                clearable
                placeholder="标题 / slug / 摘要"
                style="width: min(360px, 100%)"
                @input="onSearch"
            />
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-a-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无数据">
                <el-table-column prop="id" label="ID" width="72" />
                <el-table-column prop="title" label="标题" min-width="200" show-overflow-tooltip />
                <el-table-column label="分类" width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.category?.name || '—' }}
                    </template>
                </el-table-column>
                <el-table-column label="发布" width="88">
                    <template #default="{ row }">
                        <el-tag :type="row.is_published ? 'success' : 'info'" size="small">
                            {{ row.is_published ? '是' : '否' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="会员专享" width="100">
                    <template #default="{ row }">
                        {{ row.is_vip ? '是' : '否' }}
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="router.push({ name: 'article-edit', params: { id: row.id } })">
                            编辑
                        </el-button>
                        <el-link type="primary" :href="`/articles/${row.slug}`" target="_blank" rel="noopener">前台</el-link>
                        <el-button link type="danger" @click="removeRow(row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
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
.oc-a-alert {
    margin-bottom: 12px;
}
</style>
