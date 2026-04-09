<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const invoiceStatusOpts = enumOptions('invoiceStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const err = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({ status: 'pending', admin_note: '', invoice_file: '' });

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待开' },
    { value: 'issued', label: '已开具' },
    { value: 'rejected', label: '已拒绝' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/invoice-requests', {
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
    form.value = {
        status: r.status,
        admin_note: r.admin_note || '',
        invoice_file: r.invoice_file || '',
    };
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/invoice-requests/${editing.value.id}`, form.value);
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
        <h1 class="pg__title">发票申请</h1>
        <p class="pg__lead">表 <code>invoice_requests</code></p>
        <nav class="tabs">
            <button v-for="t in tabs" :key="t.value || 'a'" type="button" class="tab" :class="{ on: status === t.value }" @click="status = t.value">
                {{ t.label }}
            </button>
        </nav>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !editing" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>订单</th>
                        <th>邮箱</th>
                        <th>类型</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td class="mono">{{ r.order?.order_no }}</td>
                        <td>{{ r.email }}</td>
                        <td>{{ enumLabel('invoiceRequestType', r.invoice_type) }}</td>
                        <td>{{ enumLabel('invoiceStatus', r.status) }}</td>
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
                <h2>发票 #{{ editing.id }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in invoiceStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>发票文件 URL</span><input v-model="form.invoice_file" type="text" /></label>
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
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 1rem;
}
.tab {
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-size: 0.8rem;
}
.tab.on {
    background: #1e293b;
    color: #fff;
    border-color: #1e293b;
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
