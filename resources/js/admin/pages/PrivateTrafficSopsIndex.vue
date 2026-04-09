<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';
import AdminPagination from '../components/AdminPagination.vue';

const ptPlatformOpts = enumOptions('privateTrafficPlatform');
const ptCategoryOpts = enumOptions('privateTrafficCategory');
const ptVisibilityOpts = enumOptions('resourceVisibility').filter((o) => o.value === 'public' || o.value === 'vip');

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
    platform: 'wechat',
    type: 'operation',
    checklist_lines: '',
    templates_lines: '',
    metrics_lines: '',
    tools_lines: '',
    contact_note: '',
    vip_gate_engagement: false,
    visibility: 'vip',
});

function linesToArr(s) {
    return (s || '')
        .split('\n')
        .map((l) => l.trim())
        .filter(Boolean);
}

function arrToLines(a) {
    return Array.isArray(a) ? a.map((x) => (typeof x === 'string' ? x : JSON.stringify(x))).join('\n') : '';
}

function reset() {
    form.value = {
        title: '',
        slug: '',
        summary: '',
        content: '',
        platform: 'wechat',
        type: 'operation',
        checklist_lines: '',
        templates_lines: '',
        metrics_lines: '',
        tools_lines: '',
        contact_note: '',
        vip_gate_engagement: false,
        visibility: 'vip',
    };
}

async function load(page = 1) {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/private-traffic-sops', { params: { page, per_page: perPage.value, q: query.value || undefined } });
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
        platform: form.value.platform,
        type: form.value.type,
        checklist: linesToArr(form.value.checklist_lines),
        templates: linesToArr(form.value.templates_lines),
        metrics: linesToArr(form.value.metrics_lines),
        tools: linesToArr(form.value.tools_lines),
        contact_note: form.value.contact_note?.trim() || null,
        vip_gate_engagement: !!form.value.vip_gate_engagement,
        visibility: form.value.visibility,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/private-traffic-sops', payload);
        else await axios.put(`/api/admin/private-traffic-sops/${editing.value.id}`, payload);
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
        platform: r.platform,
        type: r.type,
        checklist_lines: arrToLines(r.checklist),
        templates_lines: arrToLines(r.templates),
        metrics_lines: arrToLines(r.metrics),
        tools_lines: arrToLines(r.tools),
        contact_note: r.contact_note || '',
        vip_gate_engagement: !!r.vip_gate_engagement,
        visibility: r.visibility,
    };
}

async function removeRow(r) {
    if (!confirm(`删除「${r.title}」？`)) return;
    await axios.delete(`/api/admin/private-traffic-sops/${r.id}`);
    msg.value = '已删除';
    await load(meta.value?.current_page || 1);
}

onMounted(load);
</script>

<template>
    <div class="pg">
        <div class="pg__head">
            <h1 class="pg__title">私域 SOP</h1>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </div>
        <p class="pg__lead">数据表 private_traffic_sops</p>
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
                        <th>平台</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.title }}</td>
                        <td class="mono">{{ r.slug }}</td>
                        <td>{{ enumLabel('privateTrafficPlatform', r.platform) }}</td>
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
                    <span>平台</span>
                    <select v-model="form.platform">
                        <option v-for="o in ptPlatformOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>SOP 类型</span>
                    <select v-model="form.type">
                        <option v-for="o in ptCategoryOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>可见性</span>
                    <select v-model="form.visibility">
                        <option v-for="o in ptVisibilityOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>内容 Markdown</span><textarea v-model="form.content" rows="5" /></label>
                <label class="fld"><span>联系方式（纯文本，前台展示）</span><textarea v-model="form.contact_note" rows="2" placeholder="微信 / 邮箱等" /></label>
                <label class="chk"><input v-model="form.vip_gate_engagement" type="checkbox" /> 仅 VIP 可评论并查看联系方式</label>
                <label class="fld"><span>检查清单（每行一条）</span><textarea v-model="form.checklist_lines" rows="3" placeholder="一行一项" /></label>
                <label class="fld"><span>话术模板（每行一条）</span><textarea v-model="form.templates_lines" rows="3" /></label>
                <label class="fld"><span>指标（每行一条）</span><textarea v-model="form.metrics_lines" rows="3" /></label>
                <label class="fld"><span>工具（每行一条）</span><textarea v-model="form.tools_lines" rows="3" /></label>
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
    max-width: 640px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 640px;
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
.chk {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
    line-height: 1.4;
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
