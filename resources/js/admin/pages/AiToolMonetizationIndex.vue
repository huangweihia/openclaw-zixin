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

const aiCategoryOpts = enumOptions('aiToolCategory');
const aiPricingOpts = enumOptions('aiToolMonetization');
const aiVisibilityOpts = enumOptions('resourceVisibility').filter((o) => o.value === 'public' || o.value === 'vip');

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
    tool_name: '',
    slug: '',
    tool_url: '',
    category: 'text',
    available_in_china: false,
    pricing_model: 'free',
    content: '',
    monetization_scenes_lines: '',
    prompt_templates_lines: '',
    pricing_reference_lines: '',
    channels_lines: '',
    delivery_standards_lines: '',
    visibility: 'public',
});

const columnDefs = [
    { key: 'id', label: 'ID', field: 'id' },
    { key: 'tool_name', label: '工具名', field: 'tool_name' },
    { key: 'slug', label: 'slug', field: 'slug' },
    { key: 'tool_url', label: '工具链接', field: 'tool_url', default: false },
    { key: 'category', label: '分类', field: 'category' },
    { key: 'pricing_model', label: '定价', field: 'pricing_model' },
    { key: 'available_in_china', label: '国内可用', field: 'available_in_china', default: false },
    { key: 'visibility', label: '可见性', field: 'visibility' },
    { key: 'created_at', label: '创建时间', field: 'created_at', default: false },
    { key: 'updated_at', label: '更新时间', field: 'updated_at', default: false },
];

const cols = useAdminColumnVisibility('admin:ai-tool-monetization:list', columnDefs);

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
        tool_name: '',
        slug: '',
        tool_url: '',
        category: 'text',
        available_in_china: false,
        pricing_model: 'free',
        content: '',
        monetization_scenes_lines: '',
        prompt_templates_lines: '',
        pricing_reference_lines: '',
        channels_lines: '',
        delivery_standards_lines: '',
        visibility: 'public',
    };
}

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/ai-tool-monetization', {
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
        tool_name: form.value.tool_name,
        tool_url: form.value.tool_url || null,
        category: form.value.category,
        available_in_china: form.value.available_in_china,
        pricing_model: form.value.pricing_model,
        content: form.value.content || null,
        monetization_scenes: linesToArr(form.value.monetization_scenes_lines),
        prompt_templates: linesToArr(form.value.prompt_templates_lines),
        pricing_reference: linesToArr(form.value.pricing_reference_lines),
        channels: linesToArr(form.value.channels_lines),
        delivery_standards: linesToArr(form.value.delivery_standards_lines),
        visibility: form.value.visibility,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/ai-tool-monetization', payload);
        else await axios.put(`/api/admin/ai-tool-monetization/${editing.value.id}`, payload);
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
        tool_name: r.tool_name,
        slug: r.slug,
        tool_url: r.tool_url || '',
        category: r.category,
        available_in_china: !!r.available_in_china,
        pricing_model: r.pricing_model,
        content: r.content || '',
        monetization_scenes_lines: arrToLines(r.monetization_scenes),
        prompt_templates_lines: arrToLines(r.prompt_templates),
        pricing_reference_lines: arrToLines(r.pricing_reference),
        channels_lines: arrToLines(r.channels),
        delivery_standards_lines: arrToLines(r.delivery_standards),
        visibility: r.visibility,
    };
}

async function removeRow(r) {
    try {
        await ElMessageBox.confirm(`删除「${r.tool_name}」？`, '删除', { type: 'warning' });
    } catch {
        return;
    }
    await axios.delete(`/api/admin/ai-tool-monetization/${r.id}`);
    msg.value = '已删除';
    await load(meta.value?.current_page || 1);
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="AI 工具变现" lead="ai_tool_monetization 表；列显示可自选。">
        <template #actions>
            <el-button type="primary" @click="openCreate">新建</el-button>
        </template>
        <template #toolbar>
            <el-input v-model.trim="query" clearable class="oc-toolbar-search" placeholder="搜索工具名/别名/链接" @keyup.enter="load(1)" />
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
                <el-table-column v-if="cols.show('tool_name')" prop="tool_name" label="工具" min-width="140" show-overflow-tooltip />
                <el-table-column v-if="cols.show('slug')" label="别名" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }"><span class="mono">{{ row.slug }}</span></template>
                </el-table-column>
                <el-table-column v-if="cols.show('tool_url')" label="链接" min-width="140" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.tool_url || '—' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('category')" label="分类" width="100">
                    <template #default="{ row }">{{ enumLabel('aiToolCategory', row.category) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('pricing_model')" label="定价" width="120">
                    <template #default="{ row }">{{ enumLabel('aiToolMonetization', row.pricing_model) }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('available_in_china')" label="国内" width="72">
                    <template #default="{ row }">{{ row.available_in_china ? '是' : '否' }}</template>
                </el-table-column>
                <el-table-column v-if="cols.show('visibility')" label="可见" width="100">
                    <template #default="{ row }">{{ enumLabel('resourceVisibility', row.visibility) }}</template>
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
                <el-form-item label="工具名称">
                    <el-input v-model="form.tool_name" />
                </el-form-item>
                <el-form-item v-if="mode === 'create'" label="URL 别名">
                    <el-text size="small" type="info">保存后由系统根据工具名称自动生成。</el-text>
                </el-form-item>
                <el-form-item v-else label="URL 别名">
                    <el-input :model-value="form.slug" readonly class="mono" />
                </el-form-item>
                <el-form-item label="工具链接">
                    <el-input v-model="form.tool_url" />
                </el-form-item>
                <el-form-item label="分类">
                    <el-select v-model="form.category" style="width: 100%">
                        <el-option v-for="o in aiCategoryOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="定价模式">
                    <el-select v-model="form.pricing_model" style="width: 100%">
                        <el-option v-for="o in aiPricingOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item label="可见性">
                    <el-select v-model="form.visibility" style="width: 100%">
                        <el-option v-for="o in aiVisibilityOpts" :key="o.value" :label="o.label" :value="o.value" />
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-model="form.available_in_china">国内可用</el-checkbox>
                </el-form-item>
                <el-form-item label="变现指南 HTML">
                    <el-input v-model="form.content" type="textarea" :rows="5" />
                </el-form-item>
                <el-form-item label="变现场景（每行一条）">
                    <el-input v-model="form.monetization_scenes_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="提示词模板（每行一条）">
                    <el-input v-model="form.prompt_templates_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="定价参考（每行一条）">
                    <el-input v-model="form.pricing_reference_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="渠道（每行一条）">
                    <el-input v-model="form.channels_lines" type="textarea" :rows="3" />
                </el-form-item>
                <el-form-item label="交付标准（每行一条）">
                    <el-input v-model="form.delivery_standards_lines" type="textarea" :rows="3" />
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
