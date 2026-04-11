<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';
import AdminColumnPicker from '../components/AdminColumnPicker.vue';
import { useAdminColumnVisibility } from '../composables/useAdminColumnVisibility.js';

const prTypeOpts = enumOptions('premiumResourceType');
const prVisibilityOpts = enumOptions('resourceVisibility').filter((o) => o.value === 'public' || o.value === 'vip');

const rows = ref([]);
const perPage = ref(20);
const meta = ref(null);
const total = ref(0);
const query = ref('');
const err = ref('');
const msg = ref('');
const loading = ref(false);
const mode = ref('');
const editing = ref(null);
const form = ref({
    title: '',
    slug: '',
    summary: '',
    type: 'pdf',
    content: '',
    download_link: '',
    extract_code: '',
    original_price: '',
    tags_json: '[]',
    visibility: 'vip',
});

const columnDefs = [
    { key: 'id', label: 'ID', field: 'id' },
    { key: 'title', label: '标题', field: 'title' },
    { key: 'slug', label: 'slug', field: 'slug' },
    { key: 'summary', label: '摘要', field: 'summary', default: false },
    { key: 'type', label: '类型', field: 'type' },
    { key: 'visibility', label: '可见性', field: 'visibility' },
    { key: 'download_link', label: '下载链接', field: 'download_link', default: false },
    { key: 'extract_code', label: '提取码', field: 'extract_code', default: false },
    { key: 'original_price', label: '原价', field: 'original_price', default: false },
    { key: 'download_count', label: '下载次数', field: 'download_count', default: false },
    { key: 'view_count', label: '浏览', field: 'view_count', default: false },
    { key: 'like_count', label: '点赞', field: 'like_count', default: false },
    { key: 'favorite_count', label: '收藏', field: 'favorite_count', default: false },
    { key: 'created_at', label: '创建时间', field: 'created_at', default: false },
    { key: 'updated_at', label: '更新时间', field: 'updated_at', default: false },
];

const cols = useAdminColumnVisibility('admin:premium-resources:list', columnDefs);

function reset() {
    form.value = {
        title: '',
        slug: '',
        summary: '',
        type: 'pdf',
        content: '',
        download_link: '',
        extract_code: '',
        original_price: '',
        tags_json: '[]',
        visibility: 'vip',
    };
}

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/premium-resources', {
            params: { page, per_page: perPage.value, q: query.value || undefined },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page || 1, last_page: data.last_page || 1 };
    } catch {
        err.value = '加载失败';
    } finally {
        loading.value = false;
    }
}

function onPerPageChange(v) {
    perPage.value = Number(v) || 20;
    load(1);
}

function tagsPayload() {
    try {
        const t = JSON.parse(form.value.tags_json || '[]');
        return Array.isArray(t) ? t : [];
    } catch {
        return [];
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        title: form.value.title,
        summary: form.value.summary || null,
        type: form.value.type,
        content: form.value.content || null,
        download_link: form.value.download_link || null,
        extract_code: form.value.extract_code || null,
        original_price: form.value.original_price === '' ? null : Number(form.value.original_price),
        tags: tagsPayload(),
        visibility: form.value.visibility,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/premium-resources', payload);
        else await axios.put(`/api/admin/premium-resources/${editing.value.id}`, payload);
        msg.value = '已保存';
        mode.value = '';
        editing.value = null;
        await load(meta.value?.current_page || 1);
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}

function closeFormModal() {
    mode.value = '';
    err.value = '';
    editing.value = null;
}

function onFormVisible(v) {
    if (!v) closeFormModal();
}

function openCreate() {
    err.value = '';
    mode.value = 'create';
    editing.value = null;
    reset();
}

function openEdit(r) {
    err.value = '';
    mode.value = 'edit';
    editing.value = r;
    form.value = {
        title: r.title,
        slug: r.slug,
        summary: r.summary || '',
        type: r.type,
        content: r.content || '',
        download_link: r.download_link || '',
        extract_code: r.extract_code || '',
        original_price: r.original_price != null ? String(r.original_price) : '',
        tags_json: JSON.stringify(r.tags || []),
        visibility: r.visibility,
    };
}

async function removeRow(r) {
    try {
        await ElMessageBox.confirm(`删除「${r.title}」？`, '删除', { type: 'warning' });
    } catch {
        return;
    }
    await axios.delete(`/api/admin/premium-resources/${r.id}`);
    msg.value = '已删除';
    await load(meta.value?.current_page || 1);
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="会员资源" lead="premium_resources 表；列显示可自选；slug 唯一。">
        <template #actions>
            <el-button type="primary" @click="openCreate">新建</el-button>
        </template>
        <template #toolbar>
            <el-input v-model.trim="query" clearable class="oc-toolbar-search" placeholder="搜索标题/别名" @keyup.enter="load(1)" />
            <el-button @click="load(1)">搜索</el-button>
            <AdminColumnPicker
                v-model="cols.selectedKeys"
                :definitions="cols.definitions"
                @select-all="cols.selectAll"
                @reset-default="cols.resetDefault"
            />
        </template>
        <el-alert v-if="msg" type="success" :closable="false" show-icon class="oc-alert" :title="msg" />
        <el-alert v-if="err && !mode" type="error" :closable="false" show-icon class="oc-alert" :title="err" />
        <AdminCard>
            <el-table v-loading="loading" :data="rows" stripe border style="width: 100%" empty-text="暂无数据">
                <el-table-column v-if="cols.show('id')" prop="id" label="ID" width="72" />
                <el-table-column v-if="cols.show('title')" prop="title" label="标题" min-width="160" show-overflow-tooltip />
                <el-table-column v-if="cols.show('slug')" prop="slug" label="slug" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }"><span class="mono">{{ row.slug }}</span></template>
                </el-table-column>
                <el-table-column v-if="cols.show('summary')" label="摘要" min-width="160" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.summary || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('type')" label="类型" width="100">
                    <template #default="{ row }">{{ enumLabel('premiumResourceType', row.type) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('visibility')" label="可见" width="100">
                    <template #default="{ row }">{{ enumLabel('resourceVisibility', row.visibility) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('download_link')" label="下载链接" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.download_link || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('extract_code')" prop="extract_code" label="提取码" width="88" />
                <el-table-column v-if="cols.show('original_price')" label="原价" width="88">
                    <template #default="{ row }">{{ row.original_price ?? '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('download_count')" prop="download_count" label="下载" width="80" />
                <el-table-column v-if="cols.show('view_count')" prop="view_count" label="浏览" width="72" />
                <el-table-column v-if="cols.show('like_count')" prop="like_count" label="点赞" width="72" />
                <el-table-column v-if="cols.show('favorite_count')" prop="favorite_count" label="收藏" width="72" />
                <el-table-column v-if="cols.show('created_at')" label="创建" width="168">
                    <template #default="{ row }">{{ row.created_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('updated_at')" label="更新" width="168">
                    <template #default="{ row }">{{ row.updated_at?.replace('T', ' ')?.slice(0, 19) || '—' }}</template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="removeRow(row)">删除</el-button>
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

        <el-dialog
            :model-value="!!mode"
            :title="mode === 'create' ? '新建' : '编辑'"
            width="640px"
            destroy-on-close
            @update:model-value="onFormVisible"
        >
            <el-alert v-if="err && mode" type="error" :closable="false" show-icon class="oc-alert" :title="err" />
            <el-form label-position="top">
                <el-form-item label="标题">
                    <el-input v-model="form.title" />
                </el-form-item>
                <el-form-item v-if="mode === 'create'" label="URL 别名">
                    <el-text size="small" type="info">保存后由系统根据标题自动生成。</el-text>
                </el-form-item>
                <el-form-item v-else label="URL 别名">
                    <el-input :model-value="form.slug" readonly />
                </el-form-item>
                <el-form-item label="摘要">
                    <el-input v-model="form.summary" />
                </el-form-item>
                <el-form-item label="类型">
                    <el-select v-model="form.type" style="width: 100%">
                        <el-option v-for="o in prTypeOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="可见性">
                    <el-select v-model="form.visibility" style="width: 100%">
                        <el-option v-for="o in prVisibilityOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="下载链接">
                    <el-input v-model="form.download_link" />
                </el-form-item>
                <el-form-item label="提取码">
                    <el-input v-model="form.extract_code" />
                </el-form-item>
                <el-form-item label="原价">
                    <el-input v-model="form.original_price" />
                </el-form-item>
                <el-form-item label="标签 JSON 数组">
                    <el-input v-model="form.tags_json" type="textarea" :rows="2" />
                </el-form-item>
                <el-form-item label="详情 HTML">
                    <el-input v-model="form.content" type="textarea" :rows="6" />
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="closeFormModal">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-toolbar-search {
    max-width: 360px;
    width: min(360px, 100%);
}
.oc-alert {
    margin-bottom: 12px;
}
.mono {
    font-family: ui-monospace, monospace;
}
</style>
