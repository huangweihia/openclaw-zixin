<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import { enumLabel } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import AdminColumnPicker from '../components/AdminColumnPicker.vue';
import { useAdminColumnVisibility } from '../composables/useAdminColumnVisibility.js';

const router = useRouter();
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(20);
const err = ref('');
const loading = ref(false);
let searchT;

const columnDefs = [
    { key: 'id', label: 'ID', field: 'id' },
    { key: 'name', label: '名称', field: 'name' },
    { key: 'full_name', label: '完整名', field: 'full_name', default: false },
    { key: 'description', label: '描述', field: 'description', default: false },
    { key: 'url', label: '仓库 URL', field: 'url' },
    { key: 'language', label: '语言', field: 'language' },
    { key: 'stars', label: 'Star', field: 'stars' },
    { key: 'forks', label: 'Fork', field: 'forks', default: false },
    { key: 'score', label: '评分', field: 'score', default: false },
    { key: 'tags', label: '标签', field: 'tags (json)', default: false },
    { key: 'difficulty', label: '难度', field: 'difficulty', default: false },
    { key: 'is_featured', label: '推荐', field: 'is_featured', default: false },
    { key: 'is_vip', label: 'VIP 专属', field: 'is_vip', default: false },
    { key: 'category', label: '分类', field: 'category_id → categories' },
    { key: 'created_at', label: '创建时间', field: 'created_at', default: false },
    { key: 'updated_at', label: '更新时间', field: 'updated_at', default: false },
];

const cols = useAdminColumnVisibility('admin:projects:list', columnDefs);

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/projects', {
            params: { page, per_page: perPage.value, q: q.value.trim() || undefined },
        });
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

function onSearch() {
    clearTimeout(searchT);
    searchT = setTimeout(() => load(1), 300);
}

function fmtTags(tags) {
    if (!tags || !Array.isArray(tags)) {
        return '—';
    }
    return tags.length ? tags.join('、') : '—';
}

async function removeRow(id) {
    try {
        await ElMessageBox.confirm('确定删除该项目？', '删除项目', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
        return;
    }
    err.value = '';
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
    <AdminPageShell
        title="项目管理"
        lead="支持搜索与分页；列显示可勾选 projects 表字段。编辑进入详情页。"
    >
        <template #actions>
            <el-button type="primary" @click="router.push({ name: 'project-create' })">新建项目</el-button>
        </template>
        <template #toolbar>
            <el-input
                v-model="q"
                class="oc-toolbar-search"
                clearable
                placeholder="名称 / 仓库 / URL"
                @input="onSearch"
            />
            <AdminColumnPicker
                v-model="cols.selectedKeys"
                :definitions="cols.definitions"
                @select-all="cols.selectAll"
                @reset-default="cols.resetDefault"
            />
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
        <AdminCard>
            <el-table v-loading="loading" :data="rows" stripe border style="width: 100%" empty-text="暂无数据">
                <el-table-column v-if="cols.show('id')" prop="id" label="ID" width="72" />
                <el-table-column v-if="cols.show('name')" prop="name" label="名称" min-width="140" show-overflow-tooltip />
                <el-table-column v-if="cols.show('full_name')" prop="full_name" label="完整名" min-width="160" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.full_name || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('description')" label="描述" min-width="180" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.description || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('url')" label="仓库 URL" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">
                        <el-link v-if="row.url" type="primary" :href="row.url" target="_blank" rel="noopener">{{ row.url }}</el-link>
                        <span v-else>—</span>
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('language')" prop="language" label="语言" width="100">
                    <template #default="{ row }">{{ row.language || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('stars')" prop="stars" label="Star" width="80" />
                <el-table-column v-if="cols.show('forks')" prop="forks" label="Fork" width="80" />
                <el-table-column v-if="cols.show('score')" label="评分" width="88">
                    <template #default="{ row }">{{ row.score ?? '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('tags')" label="标签" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">{{ fmtTags(row.tags) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('difficulty')" label="难度" width="100">
                    <template #default="{ row }">{{ enumLabel('projectDifficulty', row.difficulty) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('is_featured')" label="推荐" width="72">
                    <template #default="{ row }">
                        <el-tag :type="row.is_featured ? 'success' : 'info'" size="small">{{ row.is_featured ? '是' : '否' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('is_vip')" label="VIP" width="88">
                    <template #default="{ row }">
                        <el-tag :type="row.is_vip ? 'warning' : 'info'" size="small">{{ row.is_vip ? '是' : '否' }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('category')" label="分类" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.category?.name || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('created_at')" label="创建时间" width="168">
                    <template #default="{ row }">{{ row.created_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('updated_at')" label="更新时间" width="168">
                    <template #default="{ row }">{{ row.updated_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column label="操作" width="200" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="router.push({ name: 'project-edit', params: { id: row.id } })">
                            编辑
                        </el-button>
                        <el-link type="primary" :href="`/projects/${row.id}`" target="_blank" rel="noopener">前台</el-link>
                        <el-button link type="danger" @click="removeRow(row.id)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
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
.oc-toolbar-search {
    width: 100%;
    max-width: 360px;
}
.oc-c-alert {
    margin-bottom: 12px;
}
</style>
