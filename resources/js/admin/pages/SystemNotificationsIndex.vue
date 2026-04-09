<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const sysNotifPriorityOpts = enumOptions('systemNotifPriority');
const sysNotifTypeOpts = enumOptions('systemNotifType');

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
const form = ref({
    title: '',
    content: '',
    priority: 'medium',
    type: 'system',
    audience: 'all',
    action_url: '',
    is_published: false,
    expires_at: '',
});

function reset() {
    form.value = {
        title: '',
        content: '',
        priority: 'medium',
        type: 'system',
        audience: 'all',
        action_url: '',
        is_published: false,
        expires_at: '',
    };
}

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/system-notifications');
        rows.value = data.notifications ?? [];
    } catch {
        err.value = '加载失败';
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        title: form.value.title,
        content: form.value.content,
        priority: form.value.priority,
        type: form.value.type,
        audience: form.value.audience,
        action_url: form.value.action_url || null,
        is_published: form.value.is_published,
        expires_at: form.value.expires_at || null,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/system-notifications', payload);
        else await axios.put(`/api/admin/system-notifications/${editing.value.id}`, payload);
        msg.value = '已保存';
        mode.value = '';
        editing.value = null;
        await load();
    } catch {
        err.value = '保存失败';
    }
}

function closeFormModal() {
    mode.value = '';
    err.value = '';
    editing.value = null;
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
        content: r.content,
        priority: r.priority,
        type: r.type,
        audience: r.audience || 'all',
        action_url: r.action_url || '',
        is_published: !!r.is_published,
        expires_at: r.expires_at ? r.expires_at.slice(0, 16) : '',
    };
}

async function toggle(r) {
    await axios.post(`/api/admin/system-notifications/${r.id}/toggle-publish`);
    await load();
}

async function removeRow(r) {
    if (!confirm(`删除「${r.title}」？`)) return;
    await axios.delete(`/api/admin/system-notifications/${r.id}`);
    msg.value = '已删除';
    await load();
}

onMounted(load);
</script>

<template>
    <div class="pg">
        <div class="pg__head">
            <h1 class="pg__title">系统通知</h1>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </div>
        <p class="pg__lead">
            数据表 system_notifications；<strong>首次发布</strong>时会批量写入用户站内信（notifications / 功能清单 27 闭环）。
        </p>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>类型</th>
                        <th>人群</th>
                        <th>发布</th>
                        <th>站内推送</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.title }}</td>
                        <td>{{ enumLabel('systemNotifType', r.type) }}</td>
                        <td class="muted">{{ r.audience || 'all' }}</td>
                        <td>{{ r.is_published ? '是' : '否' }}</td>
                        <td>{{ r.inbox_dispatched_at ? '已推送' : '—' }}</td>
                        <td class="act">
                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>
                            <button type="button" class="lnk" @click="toggle(r)">发布开关</button>
                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
        </div>
        <div v-if="mode" class="modal" @click.self="closeFormModal">
            <div class="modal__box" @click.stop>
                <h2>{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld"><span>标题</span><input v-model="form.title" type="text" /></label>
                <label class="fld"><span>内容</span><textarea v-model="form.content" rows="5" /></label>
                <label class="fld">
                    <span>优先级</span>
                    <select v-model="form.priority">
                        <option v-for="o in sysNotifPriorityOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>类型</span>
                    <select v-model="form.type">
                        <option v-for="o in sysNotifTypeOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>派发人群</span>
                    <select v-model="form.audience">
                        <option value="all">所有用户</option>
                        <option value="non_member">非会员（普通用户）</option>
                        <option value="member">会员（VIP/SVIP/管理员）</option>
                        <option value="vip">VIP（含管理员）</option>
                        <option value="svip">SVIP（含管理员）</option>
                        <option value="admin">仅管理员</option>
                    </select>
                </label>
                <label class="fld"><span>跳转 URL</span><input v-model="form.action_url" type="text" /></label>
                <label class="fld"><span>过期时间</span><input v-model="form.expires_at" type="datetime-local" /></label>
                <label class="chk"><input v-model="form.is_published" type="checkbox" /> 已发布</label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeFormModal">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.pg__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}
.pg__title {
    margin: 0;
    font-size: 1.5rem;
}
.pg__lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
}
.ok {
    color: #166534;
}
.bad {
    color: #b91c1c;
}
.chk {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
.card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: auto;
}
.tbl {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.tbl th,
.tbl td {
    padding: 0.5rem 0.65rem;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
}
.tbl th {
    background: #f8fafc;
    font-weight: 600;
}
.act {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.lnk {
    border: none;
    background: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
}
.lnk--d {
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
    padding: 1.25rem;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}
.fld {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
.fld input,
.fld select,
.fld textarea {
    padding: 0.45rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.modal__btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.btn {
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.btn--pri {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
</style>
