<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
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
    if (!confirm('确定删除该条评价？')) return;
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
            <div class="toolbar">
                <button type="button" class="btn primary" @click="openCreate">新建评价</button>
                <button type="button" class="btn" @click="load">刷新</button>
            </div>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>排序</th>
                        <th>昵称</th>
                        <th>副标题</th>
                        <th>正文</th>
                        <th>星</th>
                        <th>发布</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.sort_order }}</td>
                        <td>{{ r.display_name }}</td>
                        <td class="muted">{{ r.caption || '—' }}</td>
                        <td class="clip">{{ r.body }}</td>
                        <td>{{ r.rating }}</td>
                        <td>{{ r.is_published ? '是' : '否' }}</td>
                        <td class="actions">
                            <button type="button" class="link" @click="openEdit(r)">编辑</button>
                            <button type="button" class="link danger" @click="remove(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无记录</p>
        </AdminCard>

        <div v-if="mode" class="modal">
            <div class="modal__box">
                <h2 class="modal__title">{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <label class="field">
                    <span>显示昵称</span>
                    <input v-model="form.display_name" type="text" maxlength="120" />
                </label>
                <label class="field">
                    <span>副标题（如 VIP 会员 · 3 个月）</span>
                    <input v-model="form.caption" type="text" maxlength="255" />
                </label>
                <label class="field">
                    <span>评价正文</span>
                    <textarea v-model="form.body" rows="4" maxlength="5000"></textarea>
                </label>
                <label class="field">
                    <span>星级 1–5</span>
                    <input v-model.number="form.rating" type="number" min="1" max="5" />
                </label>
                <label class="field">
                    <span>头像一字</span>
                    <input v-model="form.avatar_initial" type="text" maxlength="8" />
                </label>
                <div class="field">
                    <span>头像渐变（Tailwind 类名）</span>
                    <div class="preset-row">
                        <button
                            v-for="p in gradientPresets"
                            :key="p.label"
                            type="button"
                            class="preset"
                            @click="applyPreset(p)"
                        >
                            {{ p.label }}
                        </button>
                    </div>
                    <div class="row2">
                        <input v-model="form.gradient_from" type="text" placeholder="from-blue-400" />
                        <input v-model="form.gradient_to" type="text" placeholder="to-blue-600" />
                    </div>
                </div>
                <label class="field">
                    <span>排序（大靠前）</span>
                    <input v-model.number="form.sort_order" type="number" min="0" />
                </label>
                <label class="check">
                    <input v-model="form.is_published" type="checkbox" />
                    前台展示
                </label>
                <div class="modal__actions">
                    <button type="button" class="btn" @click="close">取消</button>
                    <button type="button" class="btn primary" @click="save">保存</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.5;
}
.lead code {
    font-size: 0.8em;
    background: #e2e8f0;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.toolbar {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.btn {
    padding: 0.45rem 0.75rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: 0.85rem;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    overflow: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82rem;
}
.table th,
.table td {
    padding: 0.5rem 0.65rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.muted {
    color: #64748b;
}
.clip {
    max-width: 280px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.actions {
    white-space: nowrap;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    font-size: 0.82rem;
    margin-right: 0.5rem;
}
.link.danger {
    color: #b91c1c;
}
.empty {
    padding: 1rem;
    color: #94a3b8;
    margin: 0;
}
.modal {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 80;
    padding: 1rem;
}
.modal__box {
    background: #fff;
    border-radius: 12px;
    max-width: 520px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
    padding: 1.25rem;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
}
.modal__title {
    margin: 0 0 1rem;
    font-size: 1.1rem;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}
.field input,
.field textarea {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.preset-row {
    display: flex;
    gap: 0.35rem;
    flex-wrap: wrap;
    margin-bottom: 0.35rem;
}
.preset {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    cursor: pointer;
}
.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
}
.modal__actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
</style>
