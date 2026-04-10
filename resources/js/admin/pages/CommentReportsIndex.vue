<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const commentReportStatusOpts = enumOptions('commentReportStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const loading = ref(false);
const err = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({ status: 'pending', admin_note: '' });

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待处理' },
    { value: 'processed', label: '已处理' },
    { value: 'rejected', label: '已驳回' },
];

async function load(page = 1) {
    err.value = '';
    loading.value = true;
    try {
        const { data } = await axios.get('/api/admin/comment-reports', {
            params: { page, status: status.value || undefined },
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

watch(status, () => load(1));

function clip(s) {
    if (!s) return '—';
    return s.length > 40 ? `${s.slice(0, 40)}…` : s;
}

function closeEdit() {
    editing.value = null;
    err.value = '';
}

function openEdit(r) {
    err.value = '';
    editing.value = r;
    form.value = { status: r.status, admin_note: r.admin_note || '' };
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/comment-reports/${editing.value.id}`, form.value);
        msg.value = '已更新';
        editing.value = null;
        await load(meta.value?.current_page ?? 1);
    } catch {
        err.value = '保存失败';
    }
}

onMounted(() => load(1));
</script>

<template>
    <AdminPageShell title="评论举报" lead="表 comment_reports。">
        <template #toolbar>
            <el-tabs v-model="status" class="tabs" type="card">
                <el-tab-pane v-for="t in tabs" :key="t.value || 'a'" :label="t.label" :name="t.value" />
            </el-tabs>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !editing" class="bad">{{ err }}</p>
        <AdminCard>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>评论摘要</th>
                        <th>原因</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ clip(r.comment?.content) }}</td>
                        <td>{{ r.reason }}</td>
                        <td>{{ enumLabel('commentReportStatus', r.status) }}</td>
                        <td><button type="button" class="lnk" @click="openEdit(r)">处理</button></td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
        </AdminCard>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :loading="loading"
            @update:page="load"
        />
        <div v-if="editing" class="modal" @click.self="closeEdit">
            <div class="modal__box" @click.stop>
                <h2>举报 #{{ editing.id }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <p class="hint">评论：{{ editing.comment?.content }}</p>
                <label class="fld">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in commentReportStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>管理员备注</span><textarea v-model="form.admin_note" rows="3" /></label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeEdit">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.pg__title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
}
.pg__lead {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: #64748b;
}
.tabs {
    margin-bottom: 0.75rem;
}
.ok {
    color: #166534;
}
.bad {
    color: #b91c1c;
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
.lnk {
    border: none;
    background: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
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
    max-width: 480px;
}
.hint {
    font-size: 0.82rem;
    color: #475569;
    margin: 0 0 0.75rem;
    white-space: pre-wrap;
    word-break: break-word;
}
.fld {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
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
