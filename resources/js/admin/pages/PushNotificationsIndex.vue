<script setup>

import { onMounted, ref } from 'vue';

import axios from 'axios';



/** 列表筛选：按用户 ID（由搜索选择写入） */

const filterUserId = ref('');

const filterQuery = ref('');

const filterSuggestions = ref([]);

let filterTimer = null;



const rows = ref([]);

const meta = ref(null);

const err = ref('');

const msg = ref('');

const mode = ref('');

const editing = ref(null);

const form = ref({

    user_id: '',

    title: '',

    content: '',

    action_url: '',

    data_json: '{}',

    is_sent: false,

    is_read: false,

});



/** 新建：选择收件用户 */

const recipientQuery = ref('');

const recipientSuggestions = ref([]);

const selectedRecipient = ref(null);

let recipientTimer = null;



function dataPayload() {

    try {

        const o = JSON.parse(form.value.data_json || '{}');

        return typeof o === 'object' && o !== null && !Array.isArray(o) ? o : {};

    } catch {

        return {};

    }

}



async function searchUsers(q) {

    const { data } = await axios.get('/api/admin/users', { params: { q, page: 1 } });

    return data.data ?? [];

}



function scheduleFilterSearch() {

    clearTimeout(filterTimer);

    filterTimer = setTimeout(async () => {

        const q = filterQuery.value.trim();

        if (q.length < 1) {

            filterSuggestions.value = [];

            return;

        }

        try {

            filterSuggestions.value = await searchUsers(q);

        } catch {

            filterSuggestions.value = [];

        }

    }, 280);

}



function pickFilterUser(u) {

    filterUserId.value = String(u.id);

    filterQuery.value = `${u.name} (${u.email})`;

    filterSuggestions.value = [];

    load(1);

}



function clearFilterUser() {

    filterUserId.value = '';

    filterQuery.value = '';

    filterSuggestions.value = [];

    load(1);

}



function scheduleRecipientSearch() {

    clearTimeout(recipientTimer);

    recipientTimer = setTimeout(async () => {

        const q = recipientQuery.value.trim();

        if (q.length < 1) {

            recipientSuggestions.value = [];

            return;

        }

        try {

            recipientSuggestions.value = await searchUsers(q);

        } catch {

            recipientSuggestions.value = [];

        }

    }, 280);

}



function pickRecipient(u) {

    form.value.user_id = String(u.id);

    selectedRecipient.value = u;

    recipientQuery.value = `${u.name} (${u.email})`;

    recipientSuggestions.value = [];

}



async function load(page = 1) {

    err.value = '';

    try {

        const params = { page };

        if (filterUserId.value.trim() !== '') params.user_id = filterUserId.value.trim();

        const { data } = await axios.get('/api/admin/push-notifications', { params });

        rows.value = data.data ?? [];

        meta.value = { current_page: data.current_page, last_page: data.last_page };

    } catch {

        err.value = '加载失败';

    }

}



async function save() {

    msg.value = '';

    err.value = '';

    if (mode.value === 'create' && (!form.value.user_id || Number.isNaN(Number(form.value.user_id)))) {

        err.value = '请选择用户';

        return;

    }

    const payload = {

        user_id: Number(form.value.user_id),

        title: form.value.title,

        content: form.value.content,

        action_url: form.value.action_url || null,

        data: dataPayload(),

        is_sent: form.value.is_sent,

        is_read: form.value.is_read,

    };

    try {

        if (mode.value === 'create') await axios.post('/api/admin/push-notifications', payload);

        else await axios.put(`/api/admin/push-notifications/${editing.value.id}`, payload);

        msg.value = '已保存';

        mode.value = '';

        editing.value = null;

        await load(meta.value?.current_page ?? 1);

    } catch {

        err.value = '保存失败';

    }

}



function closeFormModal() {

    mode.value = '';

    err.value = '';

    editing.value = null;

    recipientQuery.value = '';

    recipientSuggestions.value = [];

    selectedRecipient.value = null;

}



function openCreate() {

    err.value = '';

    mode.value = 'create';

    editing.value = null;

    selectedRecipient.value = null;

    recipientQuery.value = '';

    recipientSuggestions.value = [];

    form.value = {

        user_id: '',

        title: '',

        content: '',

        action_url: '',

        data_json: '{}',

        is_sent: false,

        is_read: false,

    };

}



function openEdit(r) {

    err.value = '';

    mode.value = 'edit';

    editing.value = r;

    recipientQuery.value = '';

    recipientSuggestions.value = [];

    selectedRecipient.value = r.user ? { id: r.user_id, name: r.user.name, email: r.user.email } : null;

    form.value = {

        user_id: String(r.user_id),

        title: r.title,

        content: r.content,

        action_url: r.action_url || '',

        data_json: JSON.stringify(r.data || {}),

        is_sent: !!r.is_sent,

        is_read: !!r.is_read,

    };

}



async function removeRow(r) {

    if (!confirm('删除该条推送记录？')) return;

    await axios.delete(`/api/admin/push-notifications/${r.id}`);

    msg.value = '已删除';

    await load(meta.value?.current_page ?? 1);

}



onMounted(() => load(1));

</script>



<template>

    <div class="pg">

        <div class="pg__head">

            <h1 class="pg__title">站内推送</h1>

            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>

        </div>

        <p class="pg__lead">
            表 <code>push_notifications</code>（按用户维度）。保存后会同步到前台用户「通知中心」表 <code>notifications</code>（需已执行迁移 <code>push_notification_id</code> 列）。
        </p>

        <div class="filt filt--user">

            <span>筛选用户</span>

            <div class="user-sel">

                <input

                    v-model="filterQuery"

                    type="text"

                    placeholder="输入昵称或邮箱搜索并选择"

                    autocomplete="off"

                    @input="scheduleFilterSearch"

                    @focus="scheduleFilterSearch"

                />

                <ul v-if="filterSuggestions.length" class="user-sel__dd">

                    <li v-for="u in filterSuggestions" :key="u.id" type="button" @click="pickFilterUser(u)">

                        {{ u.name }} &lt;{{ u.email }}&gt; <span class="uid">#{{ u.id }}</span>

                    </li>

                </ul>

            </div>

            <button v-if="filterUserId" type="button" class="btn btn--sm" @click="clearFilterUser">清除筛选</button>

        </div>

        <p v-if="msg" class="ok">{{ msg }}</p>

        <p v-if="err && !mode" class="bad">{{ err }}</p>

        <div class="card">

            <table class="tbl">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>用户</th>

                        <th>标题</th>

                        <th>已发/已读</th>

                        <th />

                    </tr>

                </thead>

                <tbody>

                    <tr v-for="r in rows" :key="r.id">

                        <td>{{ r.id }}</td>

                        <td>{{ r.user?.name || r.user_id }}</td>

                        <td>{{ r.title }}</td>

                        <td>{{ r.is_sent ? '是' : '否' }} / {{ r.is_read ? '是' : '否' }}</td>

                        <td class="act">

                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>

                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>

                        </td>

                    </tr>

                </tbody>

            </table>

            <p v-if="rows.length === 0" class="empty">暂无</p>

        </div>

        <div v-if="meta && meta.last_page > 1" class="pager">

            <button type="button" :disabled="meta.current_page <= 1" @click="load(meta.current_page - 1)">上一页</button>

            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>

            <button type="button" :disabled="meta.current_page >= meta.last_page" @click="load(meta.current_page + 1)">

                下一页

            </button>

        </div>

        <div v-if="mode" class="modal" @click.self="closeFormModal">

            <div class="modal__box" @click.stop>

                <h2>{{ mode === 'create' ? '新建推送' : '编辑' }}</h2>

                <p v-if="err" class="admin-modal-err">{{ err }}</p>

                <div v-if="mode === 'create'" class="fld">

                    <span>选择用户</span>

                    <div class="user-sel">

                        <input

                            v-model="recipientQuery"

                            type="text"

                            placeholder="输入昵称或邮箱搜索"

                            autocomplete="off"

                            @input="scheduleRecipientSearch"

                        />

                        <ul v-if="recipientSuggestions.length" class="user-sel__dd">

                            <li v-for="u in recipientSuggestions" :key="'r-' + u.id" @click="pickRecipient(u)">

                                {{ u.name }} &lt;{{ u.email }}&gt; <span class="uid">#{{ u.id }}</span>

                            </li>

                        </ul>

                    </div>

                    <p v-if="selectedRecipient" class="hint">已选：{{ selectedRecipient.name }}（ID {{ selectedRecipient.id }}）</p>

                </div>

                <label v-else class="fld">

                    <span>用户</span>

                    <input type="text" :value="selectedRecipient ? selectedRecipient.name + ' (#' + selectedRecipient.id + ')' : form.user_id" disabled />

                </label>

                <label class="fld"><span>标题</span><input v-model="form.title" type="text" /></label>

                <label class="fld"><span>内容</span><textarea v-model="form.content" rows="4" /></label>

                <label class="fld"><span>跳转 URL</span><input v-model="form.action_url" type="text" /></label>

                <label class="fld"><span>data JSON 对象</span><textarea v-model="form.data_json" rows="3" /></label>

                <label class="chk"><input v-model="form.is_sent" type="checkbox" /> 标记已发送</label>

                <label class="chk"><input v-model="form.is_read" type="checkbox" /> 标记已读</label>

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

    margin: 0 0 0.5rem;

    font-size: 0.85rem;

    color: #64748b;

}

.filt {

    display: flex;

    align-items: flex-start;

    gap: 0.5rem;

    margin-bottom: 1rem;

    font-size: 0.85rem;

    flex-wrap: wrap;

}

.filt--user .user-sel {

    min-width: 220px;

    flex: 1;

    max-width: 360px;

}

.user-sel {

    position: relative;

}

.user-sel input {

    width: 100%;

    padding: 0.35rem 0.5rem;

    border: 1px solid #cbd5e1;

    border-radius: 6px;

    font-size: 0.85rem;

}

.user-sel__dd {

    position: absolute;

    left: 0;

    right: 0;

    top: 100%;

    margin: 2px 0 0;

    padding: 0.25rem 0;

    list-style: none;

    background: #fff;

    border: 1px solid #e2e8f0;

    border-radius: 8px;

    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);

    max-height: 200px;

    overflow-y: auto;

    z-index: 5;

}

.user-sel__dd li {

    padding: 0.4rem 0.65rem;

    cursor: pointer;

    font-size: 0.8rem;

}

.user-sel__dd li:hover {

    background: #f1f5f9;

}

.uid {

    color: #94a3b8;

    font-size: 0.75rem;

}

.hint {

    margin: 0.35rem 0 0;

    font-size: 0.8rem;

    color: #475569;

}

.btn--sm {

    padding: 0.3rem 0.55rem;

    font-size: 0.8rem;

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

    margin-bottom: 0.5rem;

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

    max-width: 480px;

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

