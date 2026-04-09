<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const refundStatusOpts = enumOptions('refundStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const err = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({ status: 'pending', admin_note: '' });

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待处理' },
    { value: 'approved', label: '已通过' },
    { value: 'rejected', label: '已拒绝' },
    { value: 'completed', label: '已完成' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/refund-requests', {
            params: { page, status: status.value || undefined },
        });
        rows.value = data.data ?? [];
        meta.value = { current_page: data.current_page, last_page: data.last_page };
    } catch {
        err.value = '加载失败';
    }
}

watch(status, () => load(1));

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
        await axios.put(`/api/admin/refund-requests/${editing.value.id}`, form.value);
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
    <div class="pg">
        <h1 class="pg__title">退款申请</h1>
        <p class="pg__lead">表 <code>refund_requests</code>：处理状态与管理员备注</p>
        <el-tabs v-model="status" class="tabs" type="card">
            <el-tab-pane v-for="t in tabs" :key="t.value || 'a'" :label="t.label" :name="t.value" />
        </el-tabs>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !editing" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>订单</th>
                        <th>用户</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="mono">{{ r.order?.order_no }}</td>
                        <td>{{ r.user?.name }}</td>
                        <td>{{ r.refund_amount }}</td>
                        <td>{{ enumLabel('refundStatus', r.status) }}</td>
                        <td><button type="button" class="lnk" @click="openEdit(r)">处理</button></td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
        </div>
        <div v-if="meta && meta.last_page > 1" class="pager">
            <button type="button" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">上一页</button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button type="button" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">下一页</button>
        </div>
        <div v-if="editing" class="modal" @click.self="closeEdit">
            <div class="modal__box" @click.stop>
                <h2>退款 #{{ editing.id }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <p class="hint">{{ editing.description }}</p>
                <label class="fld">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in refundStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>管理员备注</span><textarea v-model="form.admin_note" rows="3" /></label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeEdit">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
    </div>
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
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
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
.pager {
    margin-top: 0.75rem;
    display: flex;
    gap: 0.65rem;
    align-items: center;
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
    max-width: 420px;
}
.hint {
    font-size: 0.82rem;
    color: #64748b;
    margin: 0 0 0.75rem;
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
