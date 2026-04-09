<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import axios from 'axios';

const TOPICS = [
    { v: 'daily', label: '日报' },
    { v: 'weekly', label: '周报' },
    { v: 'notification', label: '系统通知' },
    { v: 'promotion', label: '推广' },
];

const unsub = ref('');
const q = ref('');
const rows = ref([]);
const meta = ref(null);
const err = ref('');
const banner = ref('');
const editingId = ref(null);
const editTopics = ref([]);
const editUnsub = ref(false);

const addOpen = ref(false);
const addForm = ref({ email: '', user_id: '', topics: ['notification'] });
const addUserQuery = ref('');
const addUserSuggestions = ref([]);
const addSelectedUser = ref(null);
let addUserTimer = null;

async function searchUsers(q) {
    const { data } = await axios.get('/api/admin/users', { params: { q, page: 1 } });
    return data.data ?? [];
}

function scheduleAddUserSearch() {
    clearTimeout(addUserTimer);
    addUserTimer = setTimeout(async () => {
        const q = addUserQuery.value.trim();
        if (q.length < 1) {
            addUserSuggestions.value = [];
            return;
        }
        try {
            addUserSuggestions.value = await searchUsers(q);
        } catch {
            addUserSuggestions.value = [];
        }
    }, 280);
}

function pickAddUser(u) {
    addForm.value.user_id = String(u.id);
    addSelectedUser.value = u;
    addUserQuery.value = `${u.name} (${u.email})`;
    addUserSuggestions.value = [];
}

function clearAddUser() {
    addForm.value.user_id = '';
    addSelectedUser.value = null;
    addUserQuery.value = '';
    addUserSuggestions.value = [];
}

function topicChecked(list, v) {
    return (list || []).includes(v);
}

function toggleEditTopic(v) {
    const arr = [...editTopics.value];
    const i = arr.indexOf(v);
    if (i >= 0) {
        arr.splice(i, 1);
    } else {
        arr.push(v);
    }
    editTopics.value = arr;
}

async function load(page = 1) {
    err.value = '';
    banner.value = '';
    try {
        const params = { page };
        if (unsub.value === '1' || unsub.value === '0') {
            params.unsubscribed = unsub.value;
        }
        if (q.value.trim()) {
            params.q = q.value.trim();
        }
        const { data } = await axios.get('/api/admin/email-subscriptions', { params });
        rows.value = data.data ?? [];
        meta.value = {
            current_page: data.current_page,
            last_page: data.last_page,
        };
        if (data.meta_message) {
            banner.value = data.meta_message;
        }
    } catch (e) {
        err.value = e.response?.data?.message || '加载失败';
    }
}

onMounted(() => load(1));
watch([unsub, q], () => load(1));

function openAddModal() {
    err.value = '';
    clearAddUser();
    addOpen.value = true;
}

function closeAddModal() {
    addOpen.value = false;
    err.value = '';
    clearAddUser();
}

function startEdit(row) {
    editingId.value = row.id;
    editTopics.value = [...(row.subscribed_to || [])];
    editUnsub.value = !!row.is_unsubscribed;
}

function cancelEdit() {
    editingId.value = null;
}

async function saveEdit(row) {
    err.value = '';
    try {
        await axios.put(`/api/admin/email-subscriptions/${row.id}`, {
            subscribed_to: editTopics.value.length ? editTopics.value : ['notification'],
            is_unsubscribed: editUnsub.value,
        });
        editingId.value = null;
        await load(meta.value?.current_page || 1);
    } catch (e) {
        err.value = e.response?.data?.message || '保存失败';
    }
}

async function removeRow(row) {
    if (!confirm(`删除订阅 ${row.email}？`)) {
        return;
    }
    err.value = '';
    try {
        await axios.delete(`/api/admin/email-subscriptions/${row.id}`);
        await load(meta.value?.current_page || 1);
    } catch (e) {
        err.value = e.response?.data?.message || '删除失败';
    }
}

async function regenToken(row) {
    err.value = '';
    try {
        const { data } = await axios.post(`/api/admin/email-subscriptions/${row.id}/regenerate-token`);
        row.unsubscribe_token = data.unsubscribe_token;
    } catch (e) {
        err.value = e.response?.data?.message || '操作失败';
    }
}

async function submitAdd() {
    err.value = '';
    const payload = {
        email: addForm.value.email.trim(),
        subscribed_to: addForm.value.topics?.length ? addForm.value.topics : ['notification'],
    };
    const uid = addForm.value.user_id?.trim();
    if (uid) {
        payload.user_id = Number(uid);
    }
    try {
        await axios.post('/api/admin/email-subscriptions', payload);
        closeAddModal();
        addForm.value = { email: '', user_id: '', topics: ['notification'] };
        clearAddUser();
        await load(1);
    } catch (e) {
        err.value = e.response?.data?.message || '创建失败';
    }
}

const publicUnsubExample = computed(() => {
    const base = typeof window !== 'undefined' ? window.location.origin : '';
    return `${base}/api/email-unsubscribe/{token}`;
});
</script>

<template>
    <div>
        <h1 class="page-title">邮箱订阅</h1>
        <p class="lead">
            对应表 <code>email_subscriptions</code>，与文档「订阅类型」一致。前台可 POST
            <code>/api/email-subscriptions</code> 订阅；退订链
            <code>{{ publicUnsubExample }}</code>（将 token 替换为列表中的值）。
        </p>
        <p v-if="banner" class="banner">{{ banner }}</p>
        <p v-if="err && !addOpen" class="err">{{ err }}</p>

        <div class="toolbar">
            <el-tabs v-model="unsub" class="tabs" type="card">
                <el-tab-pane label="全部" name="" />
                <el-tab-pane label="订阅中" name="0" />
                <el-tab-pane label="已退订" name="1" />
            </el-tabs>
            <input v-model="q" class="search" type="search" placeholder="搜索邮箱…" />
            <el-button type="primary" size="small" @click="openAddModal">新增订阅</el-button>
        </div>

        <div v-if="!banner" class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>邮箱</th>
                        <th>用户</th>
                        <th>订阅类型</th>
                        <th>状态</th>
                        <th>退订令牌</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.id }}</td>
                        <td>{{ r.email }}</td>
                        <td class="muted">{{ r.user ? r.user.name + ' / ' + r.user.email : '—' }}</td>
                        <td>
                            <template v-if="editingId === r.id">
                                <label v-for="t in TOPICS" :key="t.v" class="chk">
                                    <input
                                        type="checkbox"
                                        :checked="topicChecked(editTopics, t.v)"
                                        @change="toggleEditTopic(t.v)"
                                    />
                                    {{ t.label }}
                                </label>
                            </template>
                            <span v-else class="mono">{{ (r.subscribed_to || []).join(', ') || '—' }}</span>
                        </td>
                        <td>
                            <template v-if="editingId === r.id">
                                <label class="chk">
                                    <input v-model="editUnsub" type="checkbox" />
                                    已退订
                                </label>
                            </template>
                            <span v-else>{{ r.is_unsubscribed ? '已退订' : '订阅中' }}</span>
                        </td>
                        <td class="mono token-cell">
                            <span :title="r.unsubscribe_token">{{ (r.unsubscribe_token || '').slice(0, 10) }}…</span>
                            <button type="button" class="btn sm" @click="regenToken(r)">重置</button>
                        </td>
                        <td class="actions">
                            <template v-if="editingId === r.id">
                                <button type="button" class="btn sm primary" @click="saveEdit(r)">保存</button>
                                <button type="button" class="btn sm" @click="cancelEdit">取消</button>
                            </template>
                            <template v-else>
                                <button type="button" class="btn sm" @click="startEdit(r)">编辑</button>
                                <button type="button" class="btn sm danger" @click="removeRow(r)">删除</button>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无记录</p>
        </div>

        <div v-if="meta && meta.last_page > 1" class="pager">
            <button type="button" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">上一页</button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button type="button" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">
                下一页
            </button>
        </div>

        <div v-if="addOpen" class="modal-mask" @click.self="closeAddModal">
            <div class="modal" @click.stop>
                <h2>新增邮箱订阅</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="field">
                    <span>邮箱</span>
                    <input v-model="addForm.email" type="email" required />
                </label>
                <div class="field">
                    <span>关联用户（可选）</span>
                    <p class="field-hint">搜索昵称或邮箱；留空表示访客订阅</p>
                    <div class="user-sel">
                        <input
                            v-model="addUserQuery"
                            type="text"
                            placeholder="输入昵称或邮箱搜索"
                            autocomplete="off"
                            @input="scheduleAddUserSearch"
                        />
                        <ul v-if="addUserSuggestions.length" class="user-sel__dd">
                            <li
                                v-for="u in addUserSuggestions"
                                :key="'add-u-' + u.id"
                                @click="pickAddUser(u)"
                            >
                                {{ u.name }} &lt;{{ u.email }}&gt; <span class="uid">#{{ u.id }}</span>
                            </li>
                        </ul>
                    </div>
                    <p v-if="addSelectedUser" class="field-hint">
                        已选：{{ addSelectedUser.name }}（#{{ addSelectedUser.id }}）
                        <button type="button" class="lnk-clear" @click="clearAddUser">清除</button>
                    </p>
                </div>
                <div class="field">
                    <span>订阅类型</span>
                    <div class="chk-row">
                        <label v-for="t in TOPICS" :key="t.v" class="chk">
                            <input
                                type="checkbox"
                                :checked="topicChecked(addForm.topics, t.v)"
                                @change="
                                    () => {
                                        const arr = [...addForm.topics];
                                        const i = arr.indexOf(t.v);
                                        i >= 0 ? arr.splice(i, 1) : arr.push(t.v);
                                        addForm.topics = arr.length ? arr : ['notification'];
                                    }
                                "
                            />
                            {{ t.label }}
                        </label>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn" @click="closeAddModal">取消</button>
                    <button type="button" class="btn primary" @click="submitAdd">创建</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-title {
    margin: 0 0 0.5rem;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.88rem;
    color: #475569;
    line-height: 1.55;
}
.lead code {
    font-size: 0.82em;
    background: #e2e8f0;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}
.banner {
    padding: 0.65rem 0.85rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 8px;
    color: #92400e;
    font-size: 0.88rem;
    margin-bottom: 0.75rem;
}
.err {
    color: #b91c1c;
    margin-bottom: 0.75rem;
}
.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    align-items: center;
    margin-bottom: 1rem;
}
.tabs {
    margin-bottom: 0.25rem;
}
.search {
    flex: 1;
    min-width: 180px;
    max-width: 280px;
    padding: 0.45rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1rem;
    overflow-x: auto;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.86rem;
}
.table th,
.table td {
    text-align: left;
    padding: 0.5rem 0.45rem;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}
.muted {
    color: #64748b;
}
.token-cell {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}
.actions {
    white-space: nowrap;
}
.chk {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    margin-right: 0.65rem;
    font-size: 0.82rem;
    cursor: pointer;
}
.chk-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
}
.empty {
    color: #64748b;
    padding: 1rem 0;
    text-align: center;
}
.pager {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    margin-top: 1rem;
}
.btn {
    padding: 0.45rem 0.85rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.82rem;
}
.btn.sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.btn.danger {
    color: #b91c1c;
    border-color: #fecaca;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.85rem;
    font-size: 0.88rem;
}
.field input {
    padding: 0.5rem 0.6rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}
.modal-mask {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 1rem;
}
.modal {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
}
.modal h2 {
    margin: 0 0 1rem;
    font-size: 1.1rem;
}
.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.5rem;
}
.field-hint {
    margin: 0;
    font-size: 0.78rem;
    color: #64748b;
}
.user-sel {
    position: relative;
}
.user-sel input {
    width: 100%;
    padding: 0.5rem 0.6rem;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
}
.user-sel__dd {
    list-style: none;
    margin: 0.25rem 0 0;
    padding: 0.25rem 0;
    position: absolute;
    left: 0;
    right: 0;
    z-index: 5;
    max-height: 200px;
    overflow: auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
}
.user-sel__dd li {
    padding: 0.4rem 0.65rem;
    cursor: pointer;
    font-size: 0.82rem;
}
.user-sel__dd li:hover {
    background: #f1f5f9;
}
.user-sel__dd .uid {
    color: #94a3b8;
    font-size: 0.75rem;
}
.lnk-clear {
    margin-left: 0.35rem;
    padding: 0;
    border: none;
    background: none;
    color: #2563eb;
    cursor: pointer;
    font-size: inherit;
    text-decoration: underline;
}
</style>
