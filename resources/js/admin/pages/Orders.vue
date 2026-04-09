<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const orderStatusOpts = enumOptions('orderStatus');
const orderTabs = computed(() => [{ value: '', label: '全部' }, ...orderStatusOpts]);

const status = ref('');
const orders = ref([]);
const meta = ref(null);
const loadErr = ref('');
const formErr = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({
    status: 'pending',
    remark: '',
    payment_method: '',
    payment_id: '',
    paid_at: '',
});

async function load(page = 1) {
    loadErr.value = '';
    try {
        const { data } = await axios.get('/api/admin/orders', {
            params: { page, status: status.value || undefined },
        });
        orders.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
    } catch {
        loadErr.value = '无法加载订单';
    }
}

onMounted(() => load(1));

watch(status, () => load(1));

function setTab(v) {
    status.value = v;
}

async function openEdit(o) {
    msg.value = '';
    formErr.value = '';
    try {
        const { data } = await axios.get(`/api/admin/orders/${o.id}`);
        const ord = data.order;
        editing.value = ord;
        form.value = {
            status: ord.status,
            remark: ord.remark || '',
            payment_method: ord.payment_method || '',
            payment_id: ord.payment_id || '',
            paid_at: ord.paid_at ? ord.paid_at.slice(0, 16) : '',
        };
    } catch {
        formErr.value = '加载订单失败';
    }
}

function closeEdit() {
    editing.value = null;
    formErr.value = '';
}

async function saveOrder() {
    formErr.value = '';
    try {
        await axios.put(`/api/admin/orders/${editing.value.id}`, {
            status: form.value.status,
            remark: form.value.remark || null,
            payment_method: form.value.payment_method || null,
            payment_id: form.value.payment_id || null,
            paid_at: form.value.paid_at || null,
        });
        msg.value = '订单已更新';
        closeEdit();
        await load(meta.value?.current_page ?? 1);
    } catch {
        formErr.value = '保存失败';
    }
}
</script>

<template>
    <div>
        <h1 class="page-title">订单管理</h1>
        <nav class="tabs">
            <button
                v-for="t in orderTabs"
                :key="t.value || 'all'"
                type="button"
                class="tab"
                :class="{ 'is-active': status === t.value }"
                @click="setTab(t.value)"
            >
                {{ t.label }}
            </button>
        </nav>
        <p v-if="loadErr" class="msg-err">{{ loadErr }}</p>
        <p v-if="formErr && !editing" class="msg-err">{{ formErr }}</p>
        <p v-if="msg" class="msg-ok">{{ msg }}</p>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>订单号</th>
                        <th>用户</th>
                        <th>金额</th>
                        <th>状态</th>
                        <th>商品</th>
                        <th>创建时间</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="o in orders" :key="o.id">
                        <td class="mono">{{ o.order_no }}</td>
                        <td>
                            <span v-if="o.user">{{ o.user.name }}（{{ o.user.email }}）</span>
                            <span v-else>—</span>
                        </td>
                        <td>{{ o.amount }}</td>
                        <td>{{ enumLabel('orderStatus', o.status) }}</td>
                        <td>{{ enumLabel('orderProductType', o.product_type) }} #{{ o.product_id }}</td>
                        <td class="muted">{{ o.created_at?.slice(0, 16)?.replace('T', ' ') }}</td>
                        <td>
                            <button type="button" class="link" @click="openEdit(o)">编辑</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="orders.length === 0" class="empty">暂无订单</p>
        </div>
        <div v-if="meta && meta.last_page > 1" class="pager">
            <button type="button" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">
                上一页
            </button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button
                type="button"
                :disabled="meta.current_page >= meta.last_page"
                @click="load(meta.current_page + 1)"
            >
                下一页
            </button>
        </div>

        <div v-if="editing" class="modal" @click.self="closeEdit">
            <div class="modal__box" @click.stop>
                <h2>订单 {{ editing.order_no }}</h2>
                <p v-if="formErr" class="admin-modal-err">{{ formErr }}</p>
                <p class="muted">金额 {{ editing.amount }} · 用户 {{ editing.user?.name }}</p>
                <label class="field">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in orderStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="field"><span>备注</span><textarea v-model="form.remark" rows="2" /></label>
                <label class="field"><span>支付方式</span><input v-model="form.payment_method" type="text" /></label>
                <label class="field"><span>支付 ID</span><input v-model="form.payment_id" type="text" /></label>
                <label class="field"><span>支付时间</span><input v-model="form.paid_at" type="datetime-local" /></label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeEdit">取消</button>
                    <button type="button" class="btn btn--pri" @click="saveOrder">保存</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-title {
    margin: 0 0 1rem;
    font-size: 1.5rem;
}
.tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 1rem;
}
.tab {
    padding: 0.4rem 0.85rem;
    border: 1px solid #cbd5e1;
    background: #fff;
    border-radius: 999px;
    cursor: pointer;
    font-size: 0.85rem;
}
.tab.is-active {
    background: #1e293b;
    color: #fff;
    border-color: #1e293b;
}
.msg-err {
    color: #b91c1c;
}
.msg-ok {
    color: #166534;
    margin-bottom: 0.5rem;
}
.link {
    border: none;
    background: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
    font-size: 0.85rem;
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
    max-height: 90vh;
    overflow-y: auto;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
.field input,
.field select,
.field textarea {
    padding: 0.45rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.modal__btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.5rem;
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
.table-wrap {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.06);
    overflow: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table th,
.table td {
    padding: 0.6rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}
.muted {
    color: #64748b;
    white-space: nowrap;
}
.empty {
    padding: 1.5rem;
    color: #64748b;
    margin: 0;
}
.pager {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
</style>
