<script setup>
import { onMounted, ref, watch } from 'vue';
import axios from 'axios';
import { enumLabel, enumOptions } from '../constants/labels';

const skinTypeOpts = enumOptions('skinType');

/** 与 SkinConfigController::REQUIRED_SKIN_CSS_KEYS 一致；gradient-primary 由后端根据主色/辅色自动生成，不在表单中配置 */
const REQUIRED_SKIN_CSS_KEYS = ['primary', 'secondary', 'bg-primary', 'text-primary'];

function isRequiredSkinCssVarKey(key) {
    return REQUIRED_SKIN_CSS_KEYS.includes(String(key ?? '').trim());
}

/** 与 SkinSwitcher 中 `--${key}` 对应，供运营理解各字段含义 */
const SKIN_CSS_VAR_HINTS = {
    primary: '主色：按钮、链接、强调元素的主色调',
    'primary-dark': '主色偏深：悬停、按下等较深状态',
    'primary-light': '主色偏浅：浅色背景上的次级强调',
    secondary: '辅色：与主色搭配的次要强调色',
    'bg-primary': '页面主背景色',
    'bg-secondary': '卡片、区块等次级背景（常与主背景形成层次）',
    'text-primary': '主文案：标题、正文等主要文字颜色',
    'text-secondary': '次要文案：说明、辅助信息等',
    'border-color': '边框、分割线、描边颜色',
};

function skinCssVarHint(key) {
    const k = String(key ?? '').trim();
    if (!k) {
        return '变量名将映射为前台 CSS 变量，例如 primary → --primary';
    }
    return SKIN_CSS_VAR_HINTS[k] || `自定义变量；前台使用 --${k}（名称勿含空格）`;
}

/** 新建皮肤时的默认变量结构（与前台 SkinSwitcher 使用的键一致） */
function defaultCssVarEntries() {
    return [
        { key: 'primary', value: '#3b82f6' },
        { key: 'primary-dark', value: '#2563eb' },
        { key: 'primary-light', value: '#60a5fa' },
        { key: 'secondary', value: '#0ea5e9' },
        { key: 'bg-primary', value: '#f0f9ff' },
        { key: 'bg-secondary', value: '#ffffff' },
        { key: 'text-primary', value: '#0c4a6e' },
        { key: 'text-secondary', value: '#334155' },
        { key: 'border-color', value: '#bae6fd' },
    ];
}

const rows = ref([]);
const err = ref('');
const msg = ref('');
const mode = ref('');
const editing = ref(null);
/** @type {import('vue').Ref<{ key: string; value: string }[]>} */
const cssVarEntries = ref([]);
const previewFileInput = ref(null);
const previewUploading = ref(false);
const previewUploadErr = ref('');
const previewImgBust = ref(0);
const previewImgLoadError = ref(false);
const feedbackOpen = ref(false);
const feedbackKind = ref('ok');
const feedbackText = ref('');
const form = ref({
    name: '',
    code: '',
    description: '',
    preview_image: '',
    type: 'free',
    sort: 0,
    is_active: true,
});

function reset() {
    form.value = {
        name: '',
        code: '',
        description: '',
        preview_image: '',
        type: 'free',
        sort: 0,
        is_active: true,
    };
    cssVarEntries.value = defaultCssVarEntries().map((e) => ({ ...e }));
}

function isSimpleHexColor(s) {
    const v = String(s ?? '').trim();
    return /^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v);
}

/** 供 input[type=color] 使用（仅 #rgb / #rrggbb） */
function colorInputHex(s) {
    const v = String(s ?? '').trim();
    const m = v.match(/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/);
    if (!m) return '#000000';
    let h = m[1];
    if (h.length === 3) h = h.split('').map((c) => c + c).join('');
    return `#${h.toLowerCase()}`;
}

function buildCssVariables() {
    const o = {};
    for (const e of cssVarEntries.value) {
        const k = e.key.trim();
        if (k) o[k] = e.value.trim();
    }
    delete o['gradient-primary'];
    delete o.gradient_primary;
    return o;
}

function addCssVar() {
    cssVarEntries.value.push({ key: '', value: '#3b82f6' });
}

function removeCssVar(idx) {
    const e = cssVarEntries.value[idx];
    if (!e || isRequiredSkinCssVarKey(e.key)) return;
    cssVarEntries.value.splice(idx, 1);
}

function defaultEntryValueForKey(key) {
    const found = defaultCssVarEntries().find((e) => e.key === key);
    return found ? found.value : '';
}

/** 编辑时保证四个必填键始终存在且顺序固定；不展示 gradient-primary（由服务端写入 JSON） */
function normalizeCssVarEntriesFromRaw(raw) {
    const obj = raw && typeof raw === 'object' ? raw : {};
    const out = REQUIRED_SKIN_CSS_KEYS.map((k) => ({
        key: k,
        value: String(obj[k] ?? defaultEntryValueForKey(k)),
    }));
    for (const k of Object.keys(obj)) {
        if (isRequiredSkinCssVarKey(k)) continue;
        if (k === 'gradient-primary' || k === 'gradient_primary') continue;
        out.push({ key: k, value: String(obj[k] ?? '') });
    }
    return out;
}

function validateSkinCssVariablesForSave() {
    const o = buildCssVariables();
    for (const k of REQUIRED_SKIN_CSS_KEYS) {
        const v = o[k];
        if (v == null || String(v).trim() === '') {
            return `以下主题变量为必填且不可删除：${REQUIRED_SKIN_CSS_KEYS.join('、')}。请填写「${k}」。`;
        }
    }
    const seen = new Set();
    for (const e of cssVarEntries.value) {
        const k = String(e.key ?? '').trim();
        if (!k) {
            return '存在未填写变量名的行，请补全或删除该行。';
        }
        if (seen.has(k)) {
            return `变量名重复：${k}`;
        }
        seen.add(k);
    }
    return '';
}

function showFeedback(kind, text) {
    feedbackKind.value = kind;
    feedbackText.value = text;
    feedbackOpen.value = true;
}

function closeFeedback() {
    feedbackOpen.value = false;
}

function openPreviewFilePicker() {
    previewFileInput.value?.click();
}

async function onPreviewImageChange(ev) {
    const input = ev.target;
    const file = input.files?.[0];
    if (!file) return;
    previewUploadErr.value = '';
    previewUploading.value = true;
    try {
        const fd = new FormData();
        fd.append('image', file);
        const { data } = await axios.post('/api/admin/uploads/image', fd);
        if (data.url) {
            form.value.preview_image = data.url;
            previewImgBust.value = Date.now();
            previewImgLoadError.value = false;
        }
    } catch {
        previewUploadErr.value = '上传失败（请使用 jpg/png/gif/webp，单张不超过 5MB）';
    } finally {
        previewUploading.value = false;
        input.value = '';
    }
}

async function load() {
    err.value = '';
    try {
        const { data } = await axios.get('/api/admin/skin-configs');
        rows.value = data.skins ?? [];
    } catch {
        err.value = '加载失败';
    }
}

async function save() {
    msg.value = '';
    err.value = '';
    const cssErr = validateSkinCssVariablesForSave();
    if (cssErr) {
        err.value = cssErr;
        showFeedback('err', cssErr);
        return;
    }
    const payload = {
        name: form.value.name,
        description: form.value.description || null,
        preview_image: form.value.preview_image || null,
        css_variables: buildCssVariables(),
        type: form.value.type,
        sort: Number(form.value.sort) || 0,
        is_active: form.value.is_active,
    };
    try {
        if (mode.value === 'create') await axios.post('/api/admin/skin-configs', payload);
        else await axios.put(`/api/admin/skin-configs/${editing.value.id}`, payload);
        mode.value = '';
        editing.value = null;
        previewUploadErr.value = '';
        await load();
        showFeedback('ok', '保存成功');
    } catch (e) {
        const em =
            e.response?.data?.errors?.css_variables?.[0] || e.response?.data?.message || '保存失败';
        err.value = em;
        showFeedback('err', em);
    }
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
        name: r.name,
        code: r.code,
        description: r.description || '',
        preview_image: r.preview_image || '',
        type: r.type,
        sort: r.sort ?? 0,
        is_active: !!r.is_active,
    };
    const raw = r.css_variables && typeof r.css_variables === 'object' ? r.css_variables : {};
    const keys = Object.keys(raw);
    cssVarEntries.value = keys.length
        ? normalizeCssVarEntriesFromRaw(raw)
        : defaultCssVarEntries().map((e) => ({ ...e }));
}

async function removeRow(r) {
    if (!confirm(`删除皮肤「${r.name}」？`)) return;
    await axios.delete(`/api/admin/skin-configs/${r.id}`);
    msg.value = '已删除';
    await load();
}

function closeSkinModal() {
    mode.value = '';
    err.value = '';
    previewUploadErr.value = '';
}

function previewImageSrc() {
    const u = (form.value.preview_image || '').trim();
    if (!u) return '';
    const sep = u.includes('?') ? '&' : '?';
    return `${u}${sep}v=${previewImgBust.value}`;
}

watch(
    () => form.value.preview_image,
    () => {
        previewImgLoadError.value = false;
    },
);

onMounted(load);
</script>

<template>
    <div class="pg">
        <div class="pg__head">
            <h1 class="pg__title">皮肤主题</h1>
            <button type="button" class="btn btn--pri" @click="openCreate">新建</button>
        </div>
        <p class="pg__lead">
            数据表 skin_configs；字段 css_variables 存 JSON 主题变量。必填且不可删除的键：
            <code class="inline-code">primary</code>、<code class="inline-code">secondary</code>、
            <code class="inline-code">bg-primary</code>、<code class="inline-code">text-primary</code>。
            主渐变 <code class="inline-code">gradient-primary</code> 保存时由系统根据主色与辅色自动生成并写入，无需在表单中填写。
        </p>
        <p v-if="msg" class="ok">{{ msg }}</p>
        <p v-if="err && !mode" class="bad">{{ err }}</p>
        <div class="card">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>名称</th>
                        <th>标识</th>
                        <th>类型</th>
                        <th>启用</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in rows" :key="r.id">
                        <td>{{ r.name }}</td>
                        <td class="mono">{{ r.code }}</td>
                        <td>{{ enumLabel('skinType', r.type) }}</td>
                        <td>{{ r.is_active ? '是' : '否' }}</td>
                        <td class="act">
                            <button type="button" class="lnk" @click="openEdit(r)">编辑</button>
                            <button type="button" class="lnk lnk--d" @click="removeRow(r)">删除</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="rows.length === 0" class="empty">暂无</p>
        </div>
        <div v-if="mode" class="modal" @click.self="closeSkinModal">
            <div class="modal__box modal__box--lg" @click.stop>
                <h2>{{ mode === 'create' ? '新建' : '编辑' }}</h2>
                <p v-if="err" class="admin-modal-err">{{ err }}</p>
                <label class="fld"><span>名称</span><input v-model="form.name" type="text" /></label>
                <div v-if="mode === 'create'" class="fld fld--note">
                    <span>唯一标识</span>
                    <span class="fld-tip">保存后由系统根据名称自动生成英文标识（如 my-theme-a3f2），用于前台换肤与接口；创建后不可改。</span>
                </div>
                <label v-else class="fld">
                    <span>唯一标识</span>
                    <span class="fld-tip">创建时已生成；前台与接口用此值区分主题，不可修改。</span>
                    <p class="fld-code-display mono">{{ form.code }}</p>
                </label>
                <label class="fld"><span>描述</span><input v-model="form.description" type="text" /></label>
                <label class="fld">
                    <span>预览图</span>
                    <span class="fld-tip">皮肤列表 / 换肤面板展示的缩略图；可填外链或本地上传（需服务器存在 public/storage 软链）。</span>
                    <div class="preview-img-row">
                        <input
                            v-model="form.preview_image"
                            type="text"
                            placeholder="图片地址，或点击右侧上传"
                        />
                        <input
                            ref="previewFileInput"
                            type="file"
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="visually-hidden"
                            @change="onPreviewImageChange"
                        />
                        <button
                            type="button"
                            class="btn btn--sm"
                            :disabled="previewUploading"
                            @click="openPreviewFilePicker"
                        >
                            {{ previewUploading ? '上传中…' : '上传' }}
                        </button>
                    </div>
                    <p v-if="previewUploadErr" class="fld-err">{{ previewUploadErr }}</p>
                    <div v-if="form.preview_image?.trim()" class="preview-frame">
                        <img
                            :key="previewImgBust"
                            :src="previewImageSrc()"
                            alt="预览"
                            class="preview-thumb"
                            @load="previewImgLoadError = false"
                            @error="previewImgLoadError = true"
                        />
                        <p v-if="previewImgLoadError" class="fld-err">
                            预览加载失败（常见为 404）。请在项目根执行
                            <code class="inline-code">php artisan storage:link</code>
                            ，或 Docker 下重启 php 容器以创建
                            <code class="inline-code">public/storage</code>
                            指向
                            <code class="inline-code">storage/app/public</code>
                            的软链。
                        </p>
                    </div>
                </label>
                <label class="fld">
                    <span>类型</span>
                    <select v-model="form.type">
                        <option v-for="o in skinTypeOpts" :key="o.value" :value="o.value">{{ o.label }}</option>
                    </select>
                </label>
                <label class="fld"><span>排序</span><input v-model.number="form.sort" type="number" /></label>
                <label class="chk"><input v-model="form.is_active" type="checkbox" /> 启用</label>
                <div class="css-vars">
                    <span class="css-vars__title">主题色与变量</span>
                    <p class="css-vars__hint">
                        纯色字段可用取色器；复杂值（如个别主题的自定义渐变）可在「添加变量」中用文本填写完整 CSS。顶栏主渐变由系统根据主色与辅色自动生成。标为「必填」的变量名不可改、不可移除。
                    </p>
                    <div v-for="(entry, idx) in cssVarEntries" :key="idx" class="css-var-row">
                        <div class="css-var-keycol">
                            <input
                                v-if="!isRequiredSkinCssVarKey(entry.key)"
                                v-model="entry.key"
                                type="text"
                                class="css-var-key mono"
                                placeholder="变量名，如 primary"
                                title="变量名"
                            />
                            <div v-else class="css-var-key-locked">
                                <span class="css-var-key css-var-key--readonly mono">{{ entry.key }}</span>
                                <span class="css-var-required-badge">必填</span>
                            </div>
                            <span class="css-var-hint">{{ skinCssVarHint(entry.key) }}</span>
                        </div>
                        <div class="css-var-val">
                            <template v-if="isSimpleHexColor(entry.value)">
                                <input
                                    type="color"
                                    class="css-var-swatch"
                                    :value="colorInputHex(entry.value)"
                                    @input="entry.value = $event.target.value"
                                />
                                <input v-model="entry.value" type="text" class="css-var-hex mono" />
                            </template>
                            <input
                                v-else
                                v-model="entry.value"
                                type="text"
                                class="css-var-text mono"
                                placeholder="如 linear-gradient(...)"
                            />
                        </div>
                        <button
                            type="button"
                            class="btn btn--sm"
                            :disabled="isRequiredSkinCssVarKey(entry.key)"
                            :title="isRequiredSkinCssVarKey(entry.key) ? '必填变量不可删除' : ''"
                            @click="removeCssVar(idx)"
                        >
                            移除
                        </button>
                    </div>
                    <button type="button" class="btn btn--sm" @click="addCssVar">添加变量</button>
                </div>
                <div class="modal__btns">
                    <button type="button" class="btn" @click="closeSkinModal">取消</button>
                    <button type="button" class="btn btn--pri" @click="save">保存</button>
                </div>
            </div>
        </div>
        <div
            v-if="feedbackOpen"
            class="feedback-overlay"
            role="alertdialog"
            aria-modal="true"
            aria-live="polite"
            @click.self="closeFeedback"
        >
            <div class="feedback-dialog" @click.stop>
                <p
                    class="feedback-dialog__msg"
                    :class="
                        feedbackKind === 'ok' ? 'feedback-dialog__msg--ok' : 'feedback-dialog__msg--err'
                    "
                >
                    {{ feedbackText }}
                </p>
                <button type="button" class="btn btn--pri feedback-dialog__btn" @click="closeFeedback">
                    确定
                </button>
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
.feedback-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 120;
    padding: 1rem;
}
.feedback-dialog {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem 1.35rem;
    max-width: 22rem;
    width: 100%;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
}
.feedback-dialog__msg {
    margin: 0 0 1rem;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #334155;
}
.feedback-dialog__msg--ok {
    color: #166534;
}
.feedback-dialog__msg--err {
    color: #b91c1c;
}
.feedback-dialog__btn {
    width: 100%;
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
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
}
.modal__box--lg {
    max-width: 640px;
}
.css-vars {
    margin-bottom: 0.75rem;
    padding: 0.65rem 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
.css-vars__title {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.25rem;
}
.css-vars__hint {
    margin: 0 0 0.65rem;
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.4;
}
.css-var-row {
    display: grid;
    grid-template-columns: minmax(9.5rem, 1.2fr) minmax(0, 2fr) auto;
    gap: 0.45rem;
    align-items: start;
    margin-bottom: 0.55rem;
}
.css-var-keycol {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    min-width: 0;
}
.css-var-hint {
    font-size: 0.68rem;
    color: #64748b;
    line-height: 1.35;
}
.css-var-key {
    padding: 0.35rem 0.45rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.8rem;
}
.css-var-key--readonly {
    display: inline-block;
    padding: 0.35rem 0.45rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #334155;
}
.css-var-key-locked {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    flex-wrap: wrap;
}
.css-var-required-badge {
    font-size: 0.65rem;
    font-weight: 600;
    color: #b45309;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
}
.btn--sm:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}
.css-var-val {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    min-width: 0;
}
.css-var-swatch {
    width: 2.25rem;
    height: 2rem;
    padding: 0;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    cursor: pointer;
    flex-shrink: 0;
}
.css-var-hex {
    flex: 1;
    min-width: 0;
    padding: 0.35rem 0.45rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.8rem;
}
.css-var-text {
    width: 100%;
    padding: 0.35rem 0.45rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.78rem;
}
.btn--sm {
    padding: 0.3rem 0.5rem;
    font-size: 0.78rem;
    white-space: nowrap;
}
.fld--note {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
}
.fld-code-display {
    margin: 0;
    padding: 0.4rem 0.5rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.82rem;
    color: #0f172a;
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
.preview-img-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    align-items: center;
}
.preview-img-row input[type='text'] {
    flex: 1;
    min-width: 8rem;
}
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.fld-tip {
    display: block;
    margin: 0 0 0.25rem;
    font-size: 0.72rem;
    color: #64748b;
    line-height: 1.4;
}
.fld-err {
    margin: 0;
    font-size: 0.75rem;
    color: #b91c1c;
    line-height: 1.45;
}
.inline-code {
    font-family: ui-monospace, monospace;
    font-size: 0.7rem;
    background: #f1f5f9;
    padding: 0.05rem 0.25rem;
    border-radius: 4px;
}
.preview-frame {
    margin-top: 0.4rem;
    padding: 0.35rem;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    background: #fafafa;
}
.preview-thumb {
    display: block;
    margin-top: 0.35rem;
    max-width: 100%;
    max-height: 100px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    object-fit: contain;
}
</style>
