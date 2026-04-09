<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';

const shCategoryOpts = enumOptions('sideHustleCategory');
const shTypeOpts = enumOptions('sideHustleType');
const shVisibilityOpts = enumOptions('resourceVisibility');
const shStatusOpts = enumOptions('sideHustleStatus');

const rows = ref([]);
const perPage = ref(20);
const meta = ref(null);
const total = ref(0);
const query = ref('');
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
const form = ref({
    title: '',
    slug: '',
    summary: '',
    content: '',
    category: 'online',
    type: 'other',
    startup_cost: '0',
    time_investment: '灵活安排',
    estimated_income: '0',
    actual_income: '',
    steps: '',
    tools_json: '[]',
    pitfalls_json: '[]',
    income_screenshots_json: '[]',
    willing_to_consult: false,
    contact_info: '',
    visibility: 'vip',
    status: 'pending',
    user_id: '',
});

function j(s) {
    try {
        const x = JSON.parse(s || '[]');
        return Array.isArray(x) ? x : [];
    } catch {
        return [];
    }
}

function reset() {
    form.value = {
        title: '',
        slug: '',
        summary: '',
        content: '',
        category: 'online',
        type: 'other',
        startup_cost: '0',
        time_investment: '灵活安排',
        estimated_income: '0',
        actual_income: '',
        steps: '',
        tools_json: '[]',
        pitfalls_json: '[]',
        income_screenshots_json: '[]',
        willing_to_consult: false,
        contact_info: '',
        visibility: 'vip',
        status: 'pending',
        user_id: '',
    };
}

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/side-hustle-cases', { params: { page, per_page: perPage.value, q: query.value || undefined } });
        rows.value = data.data ?? [];
        total.value = data.total ?? 0;
        meta.value = { current_page: data.current_page || 1, last_page: data.last_page || 1 };
    } catch {
        err.value = '加载失败';
    }
}
function onPerPageChange(v) {
    perPage.value = Number(v) || 20;
    load(1);
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        title: form.value.title,
        summary: form.value.summary || null,
        content: form.value.content || null,
        category: form.value.category,
        type: form.value.type,
        startup_cost: form.value.startup_cost,
        time_investment: form.value.time_investment,
        estimated_income: Number(form.value.estimated_income) || 0,
        actual_income: form.value.actual_income === '' ? null : Number(form.value.actual_income),
        steps: form.value.steps || null,
        tools: j(form.value.tools_json),
        pitfalls: j(form.value.pitfalls_json),
        income_screenshots: j(form.value.income_screenshots_json),
        willing_to_consult: form.value.willing_to_consult,
        contact_info: form.value.contact_info || null,
        visibility: form.value.visibility,
        status: form.value.status,
        user_id: form.value.user_id === '' ? null : Number(form.value.user_id),
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/side-hustle-cases', payload);
        else await axios.put(`/api/admin/side-hustle-cases/${editing.value.id}`, payload);
        msg.value = '已保存';
        mode.value = '';
        editing.value = null;
        await load(meta.value?.current_page || 1);
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
        slug: r.slug,
        summary: r.summary || '',
        content: r.content || '',
        category: r.category,
        type: r.type,
        startup_cost: r.startup_cost || '0',
        time_investment: r.time_investment || '',
        estimated_income: String(r.estimated_income ?? 0),
        actual_income: r.actual_income != null ? String(r.actual_income) : '',
        steps: r.steps || '',
        tools_json: JSON.stringify(r.tools || []),
        pitfalls_json: JSON.stringify(r.pitfalls || []),
        income_screenshots_json: JSON.stringify(r.income_screenshots || []),
        willing_to_consult: !!r.willing_to_consult,
        contact_info: r.contact_info || '',
        visibility: r.visibility,
        status: r.status,
        user_id: r.user_id != null ? String(r.user_id) : '',
    };
}

async function removeRow(r) {
    if (!confirm(`删除「${r.title}」？`)) return;
    await axios.delete(`/api/admin/side-hustle-cases/${r.id}`);
    msg.value = '已删除';
    await load(meta.value?.current_page || 1);
}

onMounted(load);
</script>

<template>
    <div class="pg">
        <div class="pg__head">
            <h1 class="pg__title">副业案例</h1>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </div>
        <p class="pg__lead">数据表 side_hustle_cases</p>
        <div class="flt">
            <input v-model.trim="query" type="text" placeholder="搜索标题/别名/摘要" @keyup.enter="load(1)" />
            <button type="button" class="btn" @click="load(1)">搜索</button>
        </div>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>标题</th>
                        <th>别名</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.title }}</td>
                        <td class="mono">{{ r.slug }}</td>
                        <td>{{ enumLabel('sideHustleStatus', r.status) }}</td>
                        <td class="act">
                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>
                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无数据</p>
        </div>
        <AdminPagination
            v-if="meta"
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="total"
            :per-page="perPage"
            @update:page="load"
            @update:per-page="onPerPageChange"
        />
        <div v-if="mode" class="modal" @click.self="closeFormModal">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld"><span>标题</span><input v-model="form.title" type="text" /></label>
                <div v-if="mode === 'create'" class="fld fld--note">
                    <span>URL 别名</span>
                    <span class="fld-hint">保存后由系统根据标题自动生成。</span>
                </div>
                <label v-else class="fld">
                    <span>URL 别名</span>
                    <p class="fld-readonly mono">{{ form.slug }}</p>
                </label>
                <label class="fld"><span>摘要</span><input v-model="form.summary" type="text" /></label>
                <label class="fld">
                    <span>分类 category</span>
                    <select v-model="form.category">
                        <option v-for="o in shCategoryOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>类型 type</span>
                    <select v-model="form.type">
                        <option v-for="o in shTypeOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>可见性</span>
                    <select v-model="form.visibility">
                        <option v-for="o in shVisibilityOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>状态</span>
                    <select v-model="form.status">
                        <option v-for="o in shStatusOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>启动成本</span><input v-model="form.startup_cost" type="text" /></label>
                <label class="fld"><span>时间投入（必填）</span><input v-model="form.time_investment" type="text" /></label>
                <label class="fld"><span>预估月收入</span><input v-model="form.estimated_income" type="text" /></label>
                <label class="fld"><span>实际月收入</span><input v-model="form.actual_income" type="text" /></label>
                <label class="fld"><span>发布用户 ID（可选）</span><input v-model="form.user_id" type="text" /></label>
                <label class="fld"><span>联系方式</span><input v-model="form.contact_info" type="text" /></label>
                <label class="chk"><input v-model="form.willing_to_consult" type="checkbox" /> 愿意咨询</label>
                <label class="fld"><span>内容 Markdown</span><textarea v-model="form.content" rows="4" /></label>
                <label class="fld"><span>步骤 Markdown</span><textarea v-model="form.steps" rows="4" /></label>
                <label class="fld"><span>工具列表（JSON 数组）</span><textarea v-model="form.tools_json" rows="2" /></label>
                <label class="fld"><span>避坑提示（JSON 数组）</span><textarea v-model="form.pitfalls_json" rows="2" /></label>
                <label class="fld"><span>收入截图（JSON 数组）</span><textarea v-model="form.income_screenshots_json" rows="2" /></label>
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
.flt {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.8rem;
}
.flt input {
    flex: 1;
    max-width: 360px;
    padding: 0.45rem 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
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
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
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
    max-width: 680px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 680px;
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
.fld--note {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}
.fld-hint {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.45;
}
.fld-readonly {
    margin: 0;
    padding: 0.45rem 0.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.82rem;
    color: #0f172a;
}
</style>
