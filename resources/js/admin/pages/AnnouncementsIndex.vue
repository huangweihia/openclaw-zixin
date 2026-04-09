<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref(''); // 'create' | 'edit' | ''
const editing = ref(null);
const form = ref({
    title: '',
    content: '',
    priority: 'medium',
    display_position: 'top',
    is_floating: false,
    cover_image: '',
    float_width: '',
    float_height: '',
    is_published: false,
    expires_at: '',
});

const priorityLabel = { low: '低', medium: '中', high: '高' };
const positionLabel = { top: '顶部跑马灯', bottom: '底部跑马灯', left: '左侧浮动', right: '右侧浮动' };

function resetForm() {
    form.value = {
        title: '',
        content: '',
        priority: 'medium',
        display_position: 'top',
        is_floating: false,
        cover_image: '',
        float_width: '',
        float_height: '',
        is_published: false,
        expires_at: '',
    };
}

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/announcements');
        rows.value = data.announcements ?? [];
    } catch {
        err.value = '加载失败（请确认已执行 migrate 且存在 announcements 表）';
    }
}

onMounted(load);

watch(
    () => form.value.display_position,
    (p) => {
        if (p === 'top' || p === 'bottom') {
            form.value.is_floating = false;
        }
    }
);

function openCreate() {
    err.value = '';
    mode.value = 'create';
    editing.value = null;
    resetForm();
}

function openEdit(row) {
    err.value = '';
    mode.value = 'edit';
    editing.value = row;
    form.value = {
        title: row.title,
        content: row.content,
        priority: row.priority || 'medium',
        display_position: row.display_position || 'top',
        is_floating: !!row.is_floating,
        cover_image: row.cover_image || '',
        float_width: row.float_width != null ? String(row.float_width) : '',
        float_height: row.float_height != null ? String(row.float_height) : '',
        is_published: !!row.is_published,
        expires_at: row.expires_at ? row.expires_at.slice(0, 16) : '',
    };
}

function close() {
    mode.value = '';
    err.value = '';
    editing.value = null;
}

function fmtAt(s) {
    if (!s) return '—';
    try {
        return new Date(s).toLocaleString('zh-CN');
    } catch {
        return s;
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        title: form.value.title,
        content: form.value.content,
        priority: form.value.priority,
        display_position: form.value.display_position,
        is_floating: form.value.is_floating,
        cover_image: form.value.cover_image?.trim() || null,
        float_width: form.value.float_width === '' || form.value.float_width == null ? null : Number(form.value.float_width),
        float_height: form.value.float_height === '' || form.value.float_height == null ? null : Number(form.value.float_height),
        is_published: form.value.is_published,
        expires_at: form.value.expires_at || null,
    };
    try {
        if (mode.value === 'create') {
            await axios.post('/api/admin/announcements', payload);
            msg.value = '已创建';
        } else if (editing.value) {
            await axios.put(`/api/admin/announcements/${editing.value.id}`, payload);
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

async function togglePublish(row) {
    try {
        await axios.post(`/api/admin/announcements/${row.id}/toggle-publish`);
        await load();
    } catch {
        err.value = '操作失败';
    }
}

async function removeRow(row) {
    if (!window.confirm(`确定删除公告「${row.title}」？`)) {
        return;
    }
    err.value = '';
    try {
        await axios.delete(`/api/admin/announcements/${row.id}`);
        msg.value = '已删除';
        await load();
    } catch {
        err.value = '删除失败';
    }
}
</script>

<template>
    <div>
        <div class="head">
            <h1 class="page-title">公告管理</h1>
            <button type="button" class="btn primary" @click="openCreate">新建公告</button>
        </div>
        <p class="lead">
            对应表 <span class="mono">announcements</span>。说明：<strong>无单独「公告类型」字段</strong>；列表中的<strong>优先级</strong>（低/中/高）用于同一条跑马灯轨道内的排序；
            <strong>展示位置</strong>决定顶部/底部横条或左/右浮动；顶部/底部跑马灯须<strong>关闭「浮动展示」</strong>。
            详见项目文档 <span class="mono">docs/后台管理-前台功能对应说明.md</span> 的「公告管理」说明。
        </p>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="err">{{ err }}</p>
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>位置</th>
                        <th>浮动</th>
                        <th>优先级</th>
                        <th>状态</th>
                        <th>发布时间</th>
                        <th>过期时间</th>
                        <th>创建人</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in rows" :key="a.id">
                        <td class="title-cell">{{ a.title }}</td>
                        <td>{{ positionLabel[a.display_position] || a.display_position || '—' }}</td>
                        <td>{{ a.is_floating ? '是' : '否' }}</td>
                        <td>{{ priorityLabel[a.priority] || a.priority }}</td>
                        <td>{{ a.is_published ? '已发布' : '草稿' }}</td>
                        <td class="mono sm">{{ fmtAt(a.published_at) }}</td>
                        <td class="mono sm">{{ fmtAt(a.expires_at) }}</td>
                        <td>{{ a.creator?.name || '—' }}</td>
                        <td class="act">
                            <button type="button" class="link" @click="openEdit(a)">编辑</button>
                            <button type="button" class="link" @click="togglePublish(a)">{{ a.is_published ? '下架' : '发布' }}</button>
                            <button type="button" class="link link--danger" @click="removeRow(a)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无公告，点击「新建公告」添加。</p>
        </div>

        <div v-if="mode" class="modal" @click.self="close">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ mode === 'create' ? '新建公告' : '编辑公告' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="field">
                    <span>标题（5–255 字）</span>
                    <input v-model="form.title" type="text" maxlength="255" />
                </label>
                <label class="field">
                    <span>内容（HTML）</span>
                    <textarea v-model="form.content" rows="12" placeholder="例如 &lt;p&gt;...&lt;/p&gt;" />
                </label>
                <label class="field">
                    <span>展示位置</span>
                    <select v-model="form.display_position">
                        <option value="top">顶部（横条跑马灯）</option>
                        <option value="bottom">底部（横条跑马灯）</option>
                        <option value="left">左侧（浮动卡片，建议配图）</option>
                        <option value="right">右侧（浮动卡片，建议配图）</option>
                    </select>
                </label>
                <label class="check">
                    <input v-model="form.is_floating" type="checkbox" />
                    浮动展示（侧栏/角标样式；顶部/底部请关闭此项以走跑马灯）
                </label>
                <label class="field">
                    <span>配图 URL（浮动公告推荐填写，便于前台展示）</span>
                    <input v-model="form.cover_image" type="text" maxlength="500" placeholder="https://..." />
                </label>
                <div class="row2">
                    <label class="field">
                        <span>浮动卡片宽度（px，80–480，留空默认）</span>
                        <input v-model="form.float_width" type="number" min="80" max="480" placeholder="如 240" />
                    </label>
                    <label class="field">
                        <span>配图区高度（px，40–600，留空默认 h-28）</span>
                        <input v-model="form.float_height" type="number" min="40" max="600" placeholder="如 120" />
                    </label>
                </div>
                <label class="field">
                    <span>优先级</span>
                    <select v-model="form.priority">
                        <option value="low">低</option>
                        <option value="medium">中</option>
                        <option value="high">高</option>
                    </select>
                </label>
                <label class="field">
                    <span>过期时间（可选）</span>
                    <input v-model="form.expires_at" type="datetime-local" />
                </label>
                <label class="check">
                    <input v-model="form.is_published" type="checkbox" />
                    立即标记为已发布（首次发布时写入发布时间）
                </label>
                <div class="btns">
                    <button type="button" class="btn" @click="close">取消</button>
                    <button type="button" class="btn primary" @click="save">保存</button>
                </div>
            </div>
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
    margin-bottom: 0.35rem;
}
.page-title {
    margin: 0;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.88rem;
    color: #64748b;
    line-height: 1.5;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    border-radius: 10px;
    overflow: auto;
    border: 1px solid #e2e8f0;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table th,
.table td {
    padding: 0.55rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.title-cell {
    max-width: 200px;
    font-weight: 600;
    color: #1e293b;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
}
.sm {
    white-space: nowrap;
}
.act {
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
}
.link--danger {
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
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 80;
}
.modal__box {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 720px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}
.field input,
.field textarea,
.field select {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
    line-height: 1.4;
}
.row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}
@media (max-width: 640px) {
    .row2 {
        grid-template-columns: 1fr;
    }
}
.btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.btn {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
</style>
