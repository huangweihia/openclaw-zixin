<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import AdminColumnPicker from '../components/AdminColumnPicker.vue';
import { useAdminColumnVisibility } from '../composables/useAdminColumnVisibility.js';

const filter = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const perPage = ref(30);
const loading = ref(false);
const err = ref('');

const columnDefs = [
    { key: 'id', label: 'ID', field: 'id' },
    { key: 'user_id', label: '用户 ID', field: 'user_id', default: false },
    { key: 'user', label: '用户', field: '→ users' },
    { key: 'commentable_type', label: '多态类型', field: 'commentable_type', default: false },
    { key: 'commentable_id', label: '对象 ID', field: 'commentable_id', default: false },
    { key: 'target', label: '目标', field: 'commentable_* + target_title' },
    { key: 'parent_id', label: '父评论 ID', field: 'parent_id', default: false },
    { key: 'content', label: '内容', field: 'content' },
    { key: 'like_count', label: '点赞数', field: 'like_count', default: false },
    { key: 'is_hidden', label: '隐藏', field: 'is_hidden' },
    { key: 'created_at', label: '创建时间', field: 'created_at', default: false },
    { key: 'updated_at', label: '更新时间', field: 'updated_at', default: false },
];

const cols = useAdminColumnVisibility('admin:comments:list', columnDefs);

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
    try {
        await ElMessageBox.confirm('确定删除该评论？', '删除评论', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
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
    <AdminPageShell title="评论管理" lead="按可见性筛选；列显示对应 comments 表字段。">
        <template #toolbar>
            <el-tabs v-model="filter" class="oc-tabs" type="card">
                <el-tab-pane label="全部" name="" />
                <el-tab-pane label="可见" name="visible" />
                <el-tab-pane label="已隐藏" name="hidden" />
            </el-tabs>
            <AdminColumnPicker
                v-model="cols.selectedKeys"
                :definitions="cols.definitions"
                @select-all="cols.selectAll"
                @reset-default="cols.resetDefault"
            />
        </template>
        <el-alert v-if="err" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
        <AdminCard>
            <el-table v-loading="loading" :data="rows" stripe border style="width: 100%" empty-text="暂无评论">
                <el-table-column v-if="cols.show('id')" prop="id" label="ID" width="72" />
                <el-table-column v-if="cols.show('user_id')" prop="user_id" label="用户 ID" width="88" />
                <el-table-column v-if="cols.show('user')" label="用户" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        {{ row.user?.name || row.user?.email || '—' }}
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('commentable_type')" label="多态类型" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.commentable_type || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('commentable_id')" prop="commentable_id" label="对象 ID" width="96" />
                <el-table-column v-if="cols.show('target')" label="目标" min-width="160" show-overflow-tooltip>
                    <template #default="{ row }">
                        <div>{{ typeLabel(row.commentable_type) }} #{{ row.commentable_id }}</div>
                        <div class="sub">{{ row.target_title }}</div>
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('parent_id')" label="父评论" width="88">
                    <template #default="{ row }">{{ row.parent_id ?? '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('content')" label="内容" min-width="220" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.content }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('like_count')" prop="like_count" label="点赞" width="72" />
                <el-table-column v-if="cols.show('is_hidden')" label="状态" width="88">
                    <template #default="{ row }">
                        <el-tag :type="row.is_hidden ? 'info' : 'success'" size="small">
                            {{ row.is_hidden ? '隐藏' : '可见' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column v-if="cols.show('created_at')" label="创建时间" width="168">
                    <template #default="{ row }">{{ row.created_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('updated_at')" label="更新时间" width="168">
                    <template #default="{ row }">{{ row.updated_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column label="操作" width="120" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="toggleHide(row)">
                            {{ row.is_hidden ? '显示' : '隐藏' }}
                        </el-button>
                        <el-button link type="danger" @click="remove(row)">删除</el-button>
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
.oc-tabs {
    margin-bottom: 0;
}
.oc-c-alert {
    margin-bottom: 12px;
}
.sub {
    font-size: 12px;
    color: var(--el-text-color-secondary);
    margin-top: 4px;
}
</style>
