<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const publishAuditStatusOpts = enumOptions('publishAuditStatus');

const status = ref('');
const rows = ref([]);
const meta = ref(null);
const total = ref(0);
const err = ref('');
const msg = ref('');
const editing = ref(null);
const form = ref({
    status: 'pending',
    reject_reason: '',
    suggest: '',
    priority: 0,
});

const tabs = [
    { value: '', label: '全部' },
    { value: 'pending', label: '待审' },
    { value: 'approved', label: '通过' },
    { value: 'rejected', label: '拒绝' },
];

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/publish-audits', {
            params: { page, status: status.value || undefined },
        });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
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
        reject_reason: r.reject_reason || '',
        suggest: r.suggest || '',
        priority: r.priority ?? 0,
    };
}

async function save() {
    err.value = '';
    try {
        await axios.put(`/api/admin/publish-audits/${editing.value.id}`, {
            status: form.value.status,
            reject_reason: form.value.reject_reason || null,
            suggest: form.value.suggest || null,
            priority: Number(form.value.priority) || 0,
        });
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
    <AdminPageShell title="发布审核记录" lead="表 publish_audits（与投稿审核配合，可手工纠偏）。">
        <template #toolbar>
            <nav class="tabs">
                <button
                    v-for="t in tabs"
                    :key="t.value || 'a'"
                    type="button"
                    class="tab"
                    :class="{ on: status === t.value }"
                    @click="status = t.value"
                >
                    {{ t.label }}
                </button>
            </nav>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !editing" class="bad">{{ err }}</p>
        <AdminCard>
            <table class="tbl">
                <thead>
                    <tr>
                        <th>投稿</th>
                        <th>用户</th>
                        <th>状态</th>
                        <th>优先级</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.user_post?.title || `#${r.publish_id}` }}</td>
                        <td>{{ r.user?.name }}</td>
                        <td>{{ enumLabel('publishAuditStatus', r.status) }}</td>
                        <td>{{ r.priority }}</td>
                        <td><button type="button" class="lnk" @click="openEdit(r)">编辑</button></td>
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
            @update:page="load"
        />
        <div v-if="editing" class="modal" @click.self="closeEdit">
            <div class="modal__box" @click.stop>
                <h2>审核记录 #{{ editing.id }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in publishAuditStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>优先级</span><input v-model.number="form.priority" type="number" /></label>
                <label class="fld"><span>拒绝原因</span><textarea v-model="form.reject_reason" rows="2" /></label>
                <label class="fld"><span>修改建议</span><textarea v-model="form.suggest" rows="2" /></label>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeEdit">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 0;
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
