<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { ElMessageBox } from 'element-plus';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
const form = ref({
    display_name: '',
    caption: '',
    body: '',
    rating: 5,
    avatar_initial: '用',
    gradient_from: 'from-blue-400',
    gradient_to: 'to-blue-600',
    sort_order: 0,
    is_published: true,
});

const gradientPresets = [
    { from: 'from-blue-400', to: 'to-blue-600', label: '蓝' },
    { from: 'from-green-400', to: 'to-green-600', label: '绿' },
    { from: 'from-purple-400', to: 'to-purple-600', label: '紫' },
    { from: 'from-orange-400', to: 'to-orange-600', label: '橙' },
];

function resetForm() {
    form.value = {
        display_name: '',
        caption: '',
        body: '',
        rating: 5,
        avatar_initial: '用',
        gradient_from: 'from-blue-400',
        gradient_to: 'to-blue-600',
        sort_order: 0,
        is_published: true,
    };
}

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/site-testimonials');
        rows.value = data.data ?? [];
    } catch {
        err.value = '加载失败（请先执行 migrate 创建 site_testimonials 表）';
    }
}

onMounted(load);

function openCreate() {
    mode.value = 'create';
    editing.value = null;
    resetForm();
}

function openEdit(row) {
    mode.value = 'edit';
    editing.value = row;
    form.value = {
        display_name: row.display_name,
        caption: row.caption || '',
        body: row.body,
        rating: row.rating ?? 5,
        avatar_initial: row.avatar_initial || '用',
        gradient_from: row.gradient_from || 'from-blue-400',
        gradient_to: row.gradient_to || 'to-blue-600',
        sort_order: row.sort_order ?? 0,
        is_published: !!row.is_published,
    };
}

function close() {
    mode.value = '';
    editing.value = null;
}

function onFormVisible(v) {
    if (!v) {
        close();
    }
}

function applyPreset(p) {
    form.value.gradient_from = p.from;
    form.value.gradient_to = p.to;
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        display_name: form.value.display_name,
        caption: form.value.caption?.trim() || null,
        body: form.value.body,
        rating: Number(form.value.rating),
        avatar_initial: form.value.avatar_initial?.trim().slice(0, 8) || '用',
        gradient_from: form.value.gradient_from,
        gradient_to: form.value.gradient_to,
        sort_order: Number(form.value.sort_order) || 0,
        is_published: !!form.value.is_published,
    };
    try {
        if (mode.value === 'create') {
            await axios.post('/api/admin/site-testimonials', payload);
            msg.value = '已创建';
        } else if (editing.value) {
            await axios.put(`/api/admin/site-testimonials/${editing.value.id}`, payload);
            msg.value = '已保存';
        }
        close();
        await load();
    } catch (e) {
        if (e.response?.status === 422 && e.response?.data?.errors) {
            const first = Object.values(e.response.data.errors)[0];
            err.value = Array.isArray(first) ? first[0] : String(first);
        } else {
            err.value = '保存失败';
        }
    }
}

async function remove(row) {
    try {
        await ElMessageBox.confirm('确定删除该条评价？', '删除评价', {
            type: 'warning',
            confirmButtonText: '删除',
            cancelButtonText: '取消',
        });
    } catch {
        return;
    }
    err.value = '';
    try {
        await axios.delete(`/api/admin/site-testimonials/${row.id}`);
        msg.value = '已删除';
        await load();
    } catch {
        err.value = '删除失败';
    }
}
</script>

<template>
    <AdminPageShell
        title="首页用户评价"
        lead="site_testimonials：前台首页读取已发布记录（最多 6 条）；无数据时首页会自动写入演示数据。"
    >
        <template #toolbar>
            <el-button type="primary" @click="openCreate">新建评价</el-button>
            <el-button @click="load">刷新</el-button>
        </template>
        <el-alert v-if="msg" type="success" :closable="false" show-icon class="oc-c-alert" :title="msg" />
        <el-alert v-if="err && !mode" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
        <AdminCard>
            <el-table :data="rows" stripe border style="width: 100%" empty-text="暂无记录">
                <el-table-column prop="sort_order" label="排序" width="72" />
                <el-table-column prop="display_name" label="昵称" min-width="100" show-overflow-tooltip />
                <el-table-column label="副标题" min-width="120" show-overflow-tooltip>
                    <template #default="{ row }">
                        <span class="muted">{{ row.caption || '—' }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="正文" min-width="200" show-overflow-tooltip>
                    <template #default="{ row }">{{ row.body }}</template>
                </el-table-column>
                <el-table-column prop="rating" label="星" width="56" />
                <el-table-column label="发布" width="72">
                    <template #default="{ row }">
                        <el-tag :type="row.is_published ? 'success' : 'info'" size="small">
                            {{ row.is_published ? '是' : '否' }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="140" fixed="right">
                    <template #default="{ row }">
                        <el-button link type="primary" @click="openEdit(row)">编辑</el-button>
                        <el-button link type="danger" @click="remove(row)">删除</el-button>
                    </template>
                </el-table-column>
            </el-table>
        </AdminCard>

        <el-dialog
            :model-value="!!mode"
            :title="mode === 'create' ? '新建' : '编辑'"
            width="520px"
            destroy-on-close
            @update:model-value="onFormVisible"
        >
            <el-alert v-if="err && mode" type="error" :closable="false" show-icon class="oc-c-alert" :title="err" />
            <el-form label-position="top" size="default">
                <el-form-item label="显示昵称">
                    <el-input v-model="form.display_name" maxlength="120" show-word-limit />
                </el-form-item>
                <el-form-item label="副标题（如 VIP 会员 · 3 个月）">
                    <el-input v-model="form.caption" maxlength="255" show-word-limit />
                </el-form-item>
                <el-form-item label="评价正文">
                    <el-input v-model="form.body" type="textarea" :rows="4" maxlength="5000" show-word-limit />
                </el-form-item>
                <el-form-item label="星级 1–5">
                    <el-input-number v-model="form.rating" :min="1" :max="5" />
                </el-form-item>
                <el-form-item label="头像一字">
                    <el-input v-model="form.avatar_initial" maxlength="8" />
                </el-form-item>
                <el-form-item label="头像渐变（Tailwind 类名）">
                    <div class="preset-row">
                        <el-button v-for="p in gradientPresets" :key="p.label" size="small" @click="applyPreset(p)">
                            {{ p.label }}
                        </el-button>
                    </div>
                    <div class="row2">
                        <el-input v-model="form.gradient_from" placeholder="from-blue-400" />
                        <el-input v-model="form.gradient_to" placeholder="to-blue-600" />
                    </div>
                </el-form-item>
                <el-form-item label="排序（大靠前）">
                    <el-input-number v-model="form.sort_order" :min="0" />
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-model="form.is_published">前台展示</el-checkbox>
                </el-form-item>
            </el-form>
            <template #footer>
                <el-button @click="close">取消</el-button>
                <el-button type="primary" @click="save">保存</el-button>
            </template>
        </el-dialog>
    </AdminPageShell>
</template>

<style scoped>
.oc-c-alert {
    margin-bottom: 12px;
}
.muted {
    color: var(--el-text-color-secondary);
}
.preset-row {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 8px;
}
.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
</style>
