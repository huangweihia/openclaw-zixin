<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const aiCategoryOpts = enumOptions('aiToolCategory');
const aiPricingOpts = enumOptions('aiToolMonetization');
const aiVisibilityOpts = enumOptions('resourceVisibility').filter((o) => o.value === 'public' || o.value === 'vip');

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
const form = ref({
    tool_name: '',
    slug: '',
    tool_url: '',
    category: 'text',
    available_in_china: false,
    pricing_model: 'free',
    content: '',
    monetization_scenes_lines: '',
    prompt_templates_lines: '',
    pricing_reference_lines: '',
    channels_lines: '',
    delivery_standards_lines: '',
    visibility: 'public',
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
        tool_name: '',
        slug: '',
        tool_url: '',
        category: 'text',
        available_in_china: false,
        pricing_model: 'free',
        content: '',
        monetization_scenes_lines: '',
        prompt_templates_lines: '',
        pricing_reference_lines: '',
        channels_lines: '',
        delivery_standards_lines: '',
        visibility: 'public',
    };
}

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/ai-tool-monetization');
        rows.value = data.tools ?? [];
    } catch {
        err.value = '加载失败';
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    const payload = {
        tool_name: form.value.tool_name,
        tool_url: form.value.tool_url || null,
        category: form.value.category,
        available_in_china: form.value.available_in_china,
        pricing_model: form.value.pricing_model,
        content: form.value.content || null,
        monetization_scenes: linesToArr(form.value.monetization_scenes_lines),
        prompt_templates: linesToArr(form.value.prompt_templates_lines),
        pricing_reference: linesToArr(form.value.pricing_reference_lines),
        channels: linesToArr(form.value.channels_lines),
        delivery_standards: linesToArr(form.value.delivery_standards_lines),
        visibility: form.value.visibility,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/ai-tool-monetization', payload);
        else await axios.put(`/api/admin/ai-tool-monetization/${editing.value.id}`, payload);
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
        tool_name: r.tool_name,
        slug: r.slug,
        tool_url: r.tool_url || '',
        category: r.category,
        available_in_china: !!r.available_in_china,
        pricing_model: r.pricing_model,
        content: r.content || '',
        monetization_scenes_lines: arrToLines(r.monetization_scenes),
        prompt_templates_lines: arrToLines(r.prompt_templates),
        pricing_reference_lines: arrToLines(r.pricing_reference),
        channels_lines: arrToLines(r.channels),
        delivery_standards_lines: arrToLines(r.delivery_standards),
        visibility: r.visibility,
    };
}

async function removeRow(r) {
    if (!confirm(`删除「${r.tool_name}」？`)) return;
    await axios.delete(`/api/admin/ai-tool-monetization/${r.id}`);
    msg.value = '已删除';
    await load();
}

onMounted(load);
</script>

<template>
    <div class="pg">
        <div class="pg__head">
            <h1 class="pg__title">AI 工具变现</h1>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </div>
        <p class="pg__lead">数据表 ai_tool_monetization</p>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>工具</th>
                        <th>别名</th>
                        <th>分类</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.tool_name }}</td>
                        <td class="mono">{{ r.slug }}</td>
                        <td>{{ enumLabel('aiToolCategory', r.category) }}</td>
                        <td class="act">
                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>
                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无数据</p>
        </div>
        <div v-if="mode" class="modal" @click.self="closeFormModal">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld"><span>工具名称</span><input v-model="form.tool_name" type="text" /></label>
                <div v-if="mode === 'create'" class="fld fld--note">
                    <span>URL 别名</span>
                    <span class="fld-hint">保存后由系统根据工具名称自动生成。</span>
                </div>
                <label v-else class="fld">
                    <span>URL 别名</span>
                    <p class="fld-readonly mono">{{ form.slug }}</p>
                </label>
                <label class="fld"><span>工具链接</span><input v-model="form.tool_url" type="text" /></label>
                <label class="fld">
                    <span>分类</span>
                    <select v-model="form.category">
                        <option v-for="o in aiCategoryOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>定价模式</span>
                    <select v-model="form.pricing_model">
                        <option v-for="o in aiPricingOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld">
                    <span>可见性</span>
                    <select v-model="form.visibility">
                        <option v-for="o in aiVisibilityOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="chk"><input v-model="form.available_in_china" type="checkbox" /> 国内可用</label>
                <label class="fld"><span>变现指南 HTML</span><textarea v-model="form.content" rows="5" /></label>
                <label class="fld"><span>变现场景（每行一条，可选）</span><textarea v-model="form.monetization_scenes_lines" rows="3" /></label>
                <label class="fld"><span>提示词模板（每行一条，可选）</span><textarea v-model="form.prompt_templates_lines" rows="3" /></label>
                <label class="fld"><span>定价参考（每行一条，可选）</span><textarea v-model="form.pricing_reference_lines" rows="3" /></label>
                <label class="fld"><span>渠道（每行一条，可选）</span><textarea v-model="form.channels_lines" rows="3" /></label>
                <label class="fld"><span>交付标准（每行一条，可选）</span><textarea v-model="form.delivery_standards_lines" rows="3" /></label>
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
