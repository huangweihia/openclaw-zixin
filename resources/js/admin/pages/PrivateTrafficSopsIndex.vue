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

const ptPlatformOpts = enumOptions('privateTrafficPlatform');
const ptCategoryOpts = enumOptions('privateTrafficCategory');
const ptVisibilityOpts = enumOptions('resourceVisibility').filter((o) => o.value === 'public' || o.value === 'vip');

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
    content: '',
    platform: 'wechat',
    type: 'operation',
    checklist_lines: '',
    templates_lines: '',
    metrics_lines: '',
    tools_lines: '',
    contact_note: '',
    vip_gate_engagement: false,
    visibility: 'vip',
});

const columnDefs = [
    { key: 'id', label: 'ID', field: 'id' },
    { key: 'title', label: '标题', field: 'title' },
    { key: 'slug', label: 'slug', field: 'slug' },
    { key: 'summary', label: '摘要', field: 'summary', default: false },
    { key: 'platform', label: '平台', field: 'platform' },
    { key: 'type', label: 'SOP 类型', field: 'type' },
    { key: 'visibility', label: '可见性', field: 'visibility' },
    { key: 'vip_gate_engagement', label: 'VIP 门槛', field: 'vip_gate_engagement', default: false },
    { key: 'created_at', label: '创建时间', field: 'created_at', default: false },
    { key: 'updated_at', label: '更新时间', field: 'updated_at', default: false },
];

const cols = useAdminColumnVisibility('admin:private-traffic-sops:list', columnDefs);

function linesToArr(s) {
    return (s || '')
        .split('\n')
        .map((l) => l.trim())
        .filter(Boolean);
}

function arrToLines(a) {
    return Array.isArray(a) ? a.map((x) => (typeof x === 'string' ? x : JSON.stringify(x))).join('\n') : '';
}

function reset() {
    form.value = {
        title: '',
        slug: '',
        summary: '',
        content: '',
        platform: 'wechat',
        type: 'operation',
        checklist_lines: '',
        templates_lines: '',
        metrics_lines: '',
        tools_lines: '',
        contact_note: '',
        vip_gate_engagement: false,
        visibility: 'vip',
    };
}

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/private-traffic-sops', {
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

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        title: form.value.title,
        summary: form.value.summary || null,
        content: form.value.content || null,
        platform: form.value.platform,
        type: form.value.type,
        checklist: linesToArr(form.value.checklist_lines),
        templates: linesToArr(form.value.templates_lines),
        metrics: linesToArr(form.value.metrics_lines),
        tools: linesToArr(form.value.tools_lines),
        contact_note: form.value.contact_note?.trim() || null,
        vip_gate_engagement: !!form.value.vip_gate_engagement,
        visibility: form.value.visibility,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/private-traffic-sops', payload);
        else await axios.put(`/api/admin/private-traffic-sops/${editing.value.id}`, payload);
        msg.value = '已保存';
        mode.value = '';
        editing.value = null;
        await load(meta.value?.current_page || 1);
    } catch {
        err.value = '保存失败';
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
        content: r.content || '',
        platform: r.platform,
        type: r.type,
        checklist_lines: arrToLines(r.checklist),
        templates_lines: arrToLines(r.templates),
        metrics_lines: arrToLines(r.metrics),
        tools_lines: arrToLines(r.tools),
        contact_note: r.contact_note || '',
        vip_gate_engagement: !!r.vip_gate_engagement,
        visibility: r.visibility,
    };
}

async function removeRow(r) {
    try {
        await ElMessageBox.confirm(`删除「${r.title}」？`, '删除', { type: 'warning' });
    } catch {
        return;
    }
    await axios.delete(`/api/admin/private-traffic-sops/${r.id}`);
    msg.value = '已删除';
    await load(meta.value?.current_page || 1);
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="私域 SOP" lead="private_traffic_sops 表；列显示可自选。">
        <template #actions>
            <el-button type="primary" @click="openCreate">新建</el-button>
        </template>
        <template #toolbar>
            <el-input v-model.trim="query" clearable class="oc-toolbar-search" placeholder="搜索标题/别名/摘要" @keyup.enter="load(1)" />
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
                <el-table-column v-if="cols.show('slug')" label="别名" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }"><span class="mono">{{ row.slug }}</span></template>
                </el-table-column>
                <el-table-column v-if="cols.show('summary')" label="摘要" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.summary || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('platform')" label="平台" width="100">
                    <template #default="{ row }">{{ enumLabel('privateTrafficPlatform', row.platform) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('type')" label="SOP 类型" width="120">
                    <template #default="{ row }">{{ enumLabel('privateTrafficCategory', row.type) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('visibility')" label="可见" width="100">
                    <template #default="{ row }">{{ enumLabel('resourceVisibility', row.visibility) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('vip_gate_engagement')" label="VIP 评论门槛" width="120">
                    <template #default="{ row }">{{ row.vip_gate_engagement ? '是' : '否' }}</template>
                </el-table-column>
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
                    <el-input :model-value="form.slug" readonly class="mono" />
                </el-form-item>
                <el-form-item label="摘要">
                    <el-input v-model="form.summary" />
                </el-form-item>
                <el-form-item label="平台">
                    <el-select v-model="form.platform" style="width: 100%">
                        <el-option v-for="o in ptPlatformOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="SOP 类型">
                    <el-select v-model="form.type" style="width: 100%">
                        <el-option v-for="o in ptCategoryOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="可见性">
                    <el-select v-model="form.visibility" style="width: 100%">
                        <el-option v-for="o in ptVisibilityOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="内容 Markdown">
                    <el-input v-model="form.content" type="textarea" :rows="5" />
                </el-form-item>
                <el-form-item label="联系方式（纯文本）">
                    <el-input v-model="form.contact_note" type="textarea" :rows="2" placeholder="微信 / 邮箱等" />
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-model="form.vip_gate_engagement">仅 VIP 可评论并查看联系方式</el-checkbox>
                </el-form-item>
                <el-form-item label="检查清单（每行一条）">
                    <el-input v-model="form.checklist_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="话术模板（每行一条）">
                    <el-input v-model="form.templates_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="指标（每行一条）">
                    <el-input v-model="form.metrics_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="工具（每行一条）">
                    <el-input v-model="form.tools_lines" type="textarea" :rows="3" />
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
