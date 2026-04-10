<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import AdminPageShell from '../components/AdminPageShell.vue';
import AdminCard from '../components/AdminCard.vue';

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref(''); // '' | 'create' | 'edit'
const editing = ref(null);
const form = ref({
    name: '',
    key: '',
    subject: '',
    content: '',
    plain_text: '',
    is_active: true,
});

const previewOpen = ref(false);
const previewLoading = ref(false);
const previewTarget = ref(null);
const previewVarsJson = ref('{\n  "code": "123456"\n}');
const previewResult = ref({ subject: '', html: '' });
const previewErr = ref('');

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/email-templates');
        rows.value = data.templates ?? [];
    } catch {
        err.value = '加载失败（请确认已执行 migrate 且存在 email_templates 表）';
    }
}

onMounted(load);

function openCreate() {
    err.value = '';
    mode.value = 'create';
    editing.value = null;
    form.value = {
        name: '',
        key: '',
        subject: '',
        content: '',
        plain_text: '',
        is_active: true,
    };
}

function openEdit(t) {
    err.value = '';
    mode.value = 'edit';
    editing.value = t;
    form.value = {
        name: t.name,
        key: t.key,
        subject: t.subject,
        content: t.content,
        plain_text: t.plain_text || '',
        is_active: !!t.is_active,
    };
}

function close() {
    mode.value = '';
    err.value = '';
    editing.value = null;
}

async function save() {
    msg.value = '';
    err.value = '';
    try {
        const body = {
            name: form.value.name,
            subject: form.value.subject,
            content: form.value.content,
            plain_text: form.value.plain_text,
            is_active: form.value.is_active,
        };
        if (mode.value === 'create') {
            await axios.post('/api/admin/email-templates', body);
        } else {
            await axios.put(`/api/admin/email-templates/${editing.value.id}`, body);
        }
        msg.value = '已保存';
        close();
        await load();
    } catch {
        err.value = '保存失败';
    }
}

async function removeRow(t) {
    if (!confirm(`删除模板「${t.key}」？`)) return;
    await axios.delete(`/api/admin/email-templates/${t.id}`);
    msg.value = '已删除';
    await load();
}

async function toggleRow(t) {
    try {
        await axios.post(`/api/admin/email-templates/${t.id}/toggle`);
        await load();
    } catch {
        err.value = '操作失败';
    }
}

function openPreview(t) {
    previewTarget.value = t;
    previewOpen.value = true;
    previewErr.value = '';
    previewResult.value = { subject: '', html: '' };
    runPreview();
}

function closePreview() {
    previewOpen.value = false;
    previewTarget.value = null;
}

async function runPreview() {
    if (!previewTarget.value) {
        return;
    }
    previewLoading.value = true;
    previewErr.value = '';
    let variables = {};
    const raw = previewVarsJson.value.trim();
    if (raw) {
        try {
            variables = JSON.parse(raw);
            if (typeof variables !== 'object' || variables === null || Array.isArray(variables)) {
                throw new Error('须为 JSON 对象');
            }
        } catch (e) {
            previewErr.value = '变量 JSON 无效：' + (e.message || '解析失败');
            previewLoading.value = false;
            return;
        }
    }
    try {
        const { data } = await axios.post(`/api/admin/email-templates/${previewTarget.value.id}/preview`, {
            variables,
        });
        previewResult.value = { subject: data.subject || '', html: data.html || '' };
    } catch (e) {
        previewErr.value = e.response?.data?.message || '预览失败';
    } finally {
        previewLoading.value = false;
    }
}
</script>

<template>
    <AdminPageShell title="邮件模板" lead="模板键 key 由系统根据名称自动生成（小写字母、数字、下划线）；创建后不可改。">
        <template #actions>
            <button type="button" class="btn primary" @click="openCreate">新建模板</button>
        </template>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="err">{{ err }}</p>
        <AdminCard>
            <table class="table">
                <thead>
                    <tr>
                        <th>键</th>
                        <th>名称</th>
                        <th>主题</th>
                        <th>状态</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in rows" :key="t.id">
                        <td class="mono">{{ t.key }}</td>
                        <td>{{ t.name }}</td>
                        <td class="subj">{{ t.subject }}</td>
                        <td>{{ t.is_active ? '启用' : '禁用' }}</td>
                        <td class="act">
                            <button type="button" class="link" @click="openEdit(t)">编辑</button>
                            <button type="button" class="link" @click="openPreview(t)">预览</button>
                            <button type="button" class="link" @click="toggleRow(t)">开关</button>
                            <button type="button" class="link danger" @click="removeRow(t)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无模板数据</p>
        </AdminCard>

        <div v-if="mode" class="modal" @click.self="close">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ mode === 'create' ? '新建模板' : `编辑：${editing?.key}` }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label v-if="mode === 'create'" class="field">
                    <span>名称</span>
                    <input v-model="form.name" type="text" />
                </label>
                <div v-if="mode === 'create'" class="field field--note">
                    <span>键 key</span>
                    <span class="field-hint">保存后由系统根据名称自动生成。</span>
                </div>
                <label v-if="mode === 'edit'" class="field">
                    <span>名称</span>
                    <input v-model="form.name" type="text" />
                </label>
                <label v-if="mode === 'edit'" class="field">
                    <span>键 key</span>
                    <p class="field-readonly mono">{{ form.key }}</p>
                </label>
                <label class="field">
                    <span>主题</span>
                    <input v-model="form.subject" type="text" />
                </label>
                <label class="field">
                    <span>HTML 内容</span>
                    <textarea v-model="form.content" rows="12" />
                </label>
                <label class="field">
                    <span>纯文本（可选）</span>
                    <textarea v-model="form.plain_text" rows="4" />
                </label>
                <label class="check">
                    <input v-model="form.is_active" type="checkbox" />
                    启用
                </label>
                <div class="btns">
                    <button type="button" class="btn" @click="close">取消</button>
                    <button type="button" class="btn primary" @click="save">保存</button>
                </div>
            </div>
        </div>

        <div v-if="previewOpen" class="modal" @click.self="closePreview">
            <div class="modal__box modal__box--preview" @click.stop>
                <h2>预览：{{ previewTarget?.key }}</h2>
                <label class="field">
                    <span>变量 JSON（可选，替换模板中双花括号变量）</span>
                    <textarea v-model="previewVarsJson" rows="5" class="mono-ta" spellcheck="false" />
                </label>
                <p v-if="previewErr" class="err">{{ previewErr }}</p>
                <div class="btns">
                    <button type="button" class="btn" @click="closePreview">关闭</button>
                    <button type="button" class="btn primary" :disabled="previewLoading" @click="runPreview">
                        {{ previewLoading ? '渲染中…' : '重新渲染' }}
                    </button>
                </div>
                <template v-if="previewResult.subject || previewResult.html">
                    <p class="pv-subj"><strong>主题：</strong>{{ previewResult.subject }}</p>
                    <div class="pv-frame-wrap">
                        <iframe class="pv-frame" title="preview" :srcdoc="previewResult.html" />
                    </div>
                </template>
            </div>
        </div>
    </AdminPageShell>
</template>

<style scoped>
.head-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}
.page-title {
    margin: 0;
    font-size: 1.5rem;
}
.lead {
    margin: 0 0 1rem;
    font-size: 0.88rem;
    color: #64748b;
}
.ok {
    color: #166534;
}
.err {
    color: #b91c1c;
}
.card {
    background: #fff;
    border-radius: 10px;
    overflow: auto;
    border: 1px solid #e2e8f0;
}
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}
.table th,
.table td {
    padding: 0.55rem 0.75rem;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}
.table th {
    background: #f8fafc;
    font-weight: 600;
}
.mono {
    font-family: ui-monospace, monospace;
    font-size: 0.78rem;
}
.subj {
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.act {
    display: flex;
    gap: 0.5rem;
}
.link {
    background: none;
    border: none;
    color: #2563eb;
    cursor: pointer;
    padding: 0;
}
.link.danger {
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
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    z-index: 80;
}
.modal__box {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    width: 100%;
    max-width: 520px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 720px;
}
.modal__box--preview {
    max-width: 900px;
    width: 100%;
}
.mono-ta {
    font-family: ui-monospace, monospace;
    font-size: 0.8rem;
}
.pv-subj {
    margin: 1rem 0 0.5rem;
    font-size: 0.88rem;
    word-break: break-all;
}
.pv-frame-wrap {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.pv-frame {
    width: 100%;
    min-height: 280px;
    height: 420px;
    border: none;
    display: block;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
    font-size: 0.85rem;
}
.field input,
.field textarea {
    padding: 0.45rem 0.55rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}
.field--note {
    margin-bottom: 0.75rem;
}
.field-hint {
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.45;
}
.field-readonly {
    margin: 0;
    padding: 0.45rem 0.55rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #0f172a;
}
.check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.85rem;
}
.btns {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.btn {
    padding: 0.45rem 0.9rem;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #fff;
    cursor: pointer;
}
.btn.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
</style>
